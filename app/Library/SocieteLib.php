<?php

namespace App\Library;

use App\Societe;
use Illuminate\Support\Facades\Log;

class SocieteLib
{

    public function authenticateByCode($code)
    {
        $result = null;

        try {
            $socConnection = Societe::select("soc_idexterne", "soc_namebase", "soc_hostbase", "soc_userbase", "soc_passbase", "soc_portbase")
                ->where("soc_code", "=", $code)
                ->where("soc_status", "=", 1)
                ->take(1)
                ->get();

            if (count($socConnection) > 0) {
                $result = $socConnection[0];
            }
        } catch (\Exception $th) {
            Log::error("SocieteLib - decrementProduitByCodebarre() => ", [$th->getMessage()]);
        }

        return $result;
    }

    public function authenticateByIde($ide)
    {
        $result = null;

        try {
            $socConnection = Societe::select("soc_idexterne", "soc_namebase", "soc_hostbase", "soc_userbase", "soc_passbase", "soc_portbase")
                ->where("soc_idexterne", "=", $ide)
                ->where("soc_status", "=", 1)
                ->take(1)
                ->get();

            if (count($socConnection) > 0) {
                $result = $socConnection[0];
            }
        } catch (\Exception $th) {
            Log::error("SocieteLib - authenticateByIde() => ", [$th->getMessage()]);
        }

        return $result;
    }
}
