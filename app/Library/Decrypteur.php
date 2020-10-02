<?php

namespace App\Library;
use Illuminate\Support\Facades\Crypt;

class Decrypteur
{
    public $ID;
    public $IDS;
    public $DATE;
    public $MACHINE;
    public $PARSED;


    public function parse($token)
    {
        $j_token = Crypt::decryptString($token);

        if ($j_token) {
            $a_token = json_decode($j_token);

            if (is_array($a_token) && count($a_token) === 4) {
                $this->ID       = $a_token[0];
                $this->IDS      = $a_token[1];
                $this->DATE     = $a_token[2];
                $this->MACHINE  = $a_token[3];
                $this->PARSED   = $a_token;
            }
        }

        return $this;
    }
}
