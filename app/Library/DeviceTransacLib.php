<?php

namespace App\Library;

use App\Device;
use App\Transac;
use App\TransacDetail;
use App\DeviceUser;
use App\Library\Guid;
use Illuminate\Support\Facades\Log;

class DeviceTransacLib
{

    public function __construct($conn = null)
    {
        if ($conn) {
            Device::setInfosDb($conn);
            Transac::setInfosDb($conn);
            TransacDetail::setInfosDb($conn);
            DeviceUser::setInfosDb($conn);
        }
    }

    public function setTransactionAuth($machine, $login)
    {
        $objGuid    = new Guid();

        $guidTrans  = $objGuid->generatGuid();

        $guidDevice = md5($machine);

        try {
            $device = Device::select("dvc_idexterne")
                ->where("dvc_idexterne", "=", $guidDevice)
                ->where("dvc_statut", "=", "ACT")
                ->take(1)
                ->get();

            if (count($device) < 1) {
                Device::updateOrCreate(
                    [
                        "dvc_idexterne" => $guidDevice
                    ],
                    [
                        "dvc_detail"    => $machine,
                        "dvc_transac"   => $guidTrans,
                        "dvc_statut"    => 'TEN',
                        "dvc_datecrea"  => date('Y-m-d H:i:s')
                    ]
                );
            }


            Transac::create([
                "tsc_idexterne" => $guidTrans,
                "tsc_action"    => 'AUT',
                "tsc_statut"    => 'ENC',
                "tsc_montant"   => 0,
                "tsc_origin"    => "CSS",
                "tsc_user"      => $login,
                "tsc_device"    => $guidDevice
            ]);

            return array(
                "transac" => $guidTrans,
                "device"  => $guidDevice
            );
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - setTransactionAuth() => ", [$th->getMessage()]);
            return array();
        }
    }

    public function updateFailedAuth($ideTransac)
    {
        try {
            Transac::where("tsc_idexterne", "=", $ideTransac)
                ->update([
                    "tsc_statut" => "FAI"
                ]);
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - updateFailedAuth() => ", [$th->getMessage()]);
        }
    }

    public function updateSentSuccess($ide)
    {
        try {
            Transac::where("tsc_idexterne", "=", $ide)
                ->update([
                    "tsc_statut" => "ENV",
                    "tsc_origin" => "WS"
                ]);
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - updateSentSuccess() => ", [$th->getMessage()]);
        }
    }

    public function updateSentFailed($ide)
    {
        try {
            Transac::where("tsc_idexterne", "=", $ide)
                ->update([
                    "tsc_statut" => "FAI",
                    "tsc_origin" => "WS"
                ]);
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - updateSentFailed() => ", [$th->getMessage()]);
        }
    }


    public function updateAuthorizedDevice($ide)
    {
        try {
            Device::where("dvc_idexterne", "=", $ide)
                ->update([
                    "dvc_statut" => "ACT",
                ]);
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - updateAuthorizedDevice() => ", [$th->getMessage()]);
        }
    }

    public function setSellTransac($guidTransac, $montant, $userId, $deviceIde)
    {
        $result = true;

        try {
            $device = Device::select("dvc_idexterne")
                ->where("dvc_idexterne", "=", $deviceIde)
                ->where("dvc_statut", "=", "ACT")
                ->take(1)
                ->get();

            if (count($device) < 1) {
                throw new \Exception("device not found");
            }

            Device::where("dvc_idexterne", "=", $deviceIde)
                ->update([
                    "dvc_transac"   => $guidTransac
                ]);

            Transac::create([
                "tsc_idexterne" => $guidTransac,
                "tsc_action"    => 'SELL',
                "tsc_statut"    => "SUC",
                "tsc_montant"   => $montant,
                "tsc_origin"    => "CSS",
                "tsc_user"      => $userId,
                "tsc_device"    => $deviceIde,
                "tsc_datecrea"  => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - setSellTransac() => ", [$th->getMessage()]);
            $result = false;
        }

        return $result;
    }

    public function isDeviceAllowedOnUser($idd, $idu)
    {
        $isAllowed = false;

        try {
            $result = DeviceUser::select("*")
                ->where("dvcu_device", "=", $idd)
                ->where("dvcu_user", "=", $idu)
                ->take(1)
                ->get();

            if (count($result) > 0) {
                $isAllowed = true;
            }
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - isDeviceAllowedOnUser() => ", [$th->getMessage()]);
        }

        return $isAllowed;
    }

    public function setSellTransacDetail(array $detail)
    {
        $result = true;
        try {
            TransacDetail::insert($detail);
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - setSellTransacDetail() => ", [$th->getMessage()]);
            $result = false;
        }

        return $result;
    }
}
