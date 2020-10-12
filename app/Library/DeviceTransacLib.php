<?php

namespace App\Library;

use App\Device;
use App\Transac;
use App\TransacDetail;
use App\Maguserdevice;
use App\Library\Guid;
use Illuminate\Support\Facades\Log;

class DeviceTransacLib
{

    public $guidTrans;

    public function __construct($conn = null)
    {
        if ($conn) {
            Device::setInfosDb($conn);
            Transac::setInfosDb($conn);
            TransacDetail::setInfosDb($conn);
            Maguserdevice::setInfosDb($conn);
        }

        $objGuid    = new Guid();
        $this->guidTrans  = $objGuid->generatGuid();
    }

    /*
    * Verifiy if device exist and is actif
    * @param $machine string
    * @return boolean
    */
    public function isDeviceExist($machine) 
    {
        try {
            $device = Device::select("dvc_idexterne")
                ->where("dvc_idexterne", "=", md5($machine))
                ->where("dvc_statut", "=", "ACT")
                ->take(1)
                ->get();
    
                return count($device) > 0;
        } catch (\Exception $th) {
            Log::error("DeviceTransacLib - isDeviceExist() => ", [$th->getMessage()]);
            return false;
        }
    }


    /*
    * Create new device
    * @param $machine string
    * @return void
    */
    public function setNewDevice($machine)
    {
        Device::updateOrCreate(
            [
                "dvc_idexterne" => md5($machine)
            ],
            [
                "dvc_detail"    => $machine,
                "dvc_statut"    => 'TEN',
                "dvc_datecrea"  => date('Y-m-d H:i:s')
            ]
        );
    }



    public function setTransactionAuth($machine, $login)
    {
        $guidDevice = md5($machine);

        try {
            $device = Device::select("dvc_idexterne", "magudvc_mag")
                ->join("tr_maguserdevice", 'dvc_idexterne', 'magudvc_device')
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
                "device"  => $guidDevice,
                "magasin" => $device->magudvc_mag
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
            $result = Maguserdevice::select("*")
                ->where("magudvc_device", "=", $idd)
                ->where("magudvc_user", "=", $idu)
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
