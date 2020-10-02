<?php

namespace App\Library;
use App\Societe;
use App\Utilisateur;
use Illuminate\Support\Facades\Crypt;

class AuthLib
{
    public $TOKEN;


    public function authResponse(Utilisateur $objUser, Societe $objSociete, $machine)
    {
        $date = date('Y-m-d H:i:s');
        $data = [$objUser->utl_idexterne, $objSociete->soc_idexterne, $date, $machine];

        $this->TOKEN = Crypt::encryptString(json_encode($data));

        return $this;
    }
}
