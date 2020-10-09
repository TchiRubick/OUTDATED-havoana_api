<?php

namespace App\Library;

use Illuminate\Support\Facades\Log;
use App\Maguserdevice;

class MaguserdeviceLib
{
    public function __construct($conn = null)
    {
        if ($conn) {
            Maguserdevice::setInfosDb($conn);
        }
    }

    public function getMagasinConnection($device, $idUser) 
    {
        try {
            $result = Maguserdevice::select("magudvc_device", "magudvc_user", "magudvc_mag")
                ->where("magudvc_device", "=", $device)
                ->where("magudvc_user", "=", $idUser)
                ->take(1)
                ->get();

            return isset($result[0]) ? $result[0] : [];
        } catch (\Exception $th) {
            Log::error("MaguserdeviceLib - getMagasinConnection() => ", [$th->getMessage()]);
            return array();
        }
    }
}