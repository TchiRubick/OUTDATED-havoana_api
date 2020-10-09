<?php

namespace App\Library;

use Illuminate\Support\Facades\Log;
use App\Vente;

class VenteLib
{
    public function __construct($conn = null)
    {
        if ($conn) {
            Vente::setInfosDb($conn);
        }
    }

    public function venteSet($data) 
    {
        try {
            Vente::create($data);

            return true;
        } catch (\Exception $th) {
            Log::error("VenteLib - venteSet() => ", [$th->getMessage()]);
        }

        return false;
    }
}