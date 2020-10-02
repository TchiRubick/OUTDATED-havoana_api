<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Library\ProduitLib;

/**
 * Logique des code erreur.
 * digit 1   : Degré de l'erreur (1 -> 9)
 * digit 2   : Niveau de blocage (1 -> 9)
 * digit 3-4 : Etape dans le code (10 -> 99)
 */

class ProduitController extends BaseController
{
    private $_code = 0;
    private $_conn;

    public static $SUCCESS_RESPONSE = array(
        "error" => 0,
        "code" => 0,
        "response" => ''
    );

    public static $ERROR_RESPONSE = array(
        "error" => 1,
        "code" => 0,
        "response" => ''
    );

    public function __construct()
    {
        $this->middleware('apiKey');
        $this->middleware('tokenRecognition');
    }

    public function getAllProduit(Request $request)
    {
        try {
            $this->_code = 1010;
            $this->_conn = $request->get('connection');

            $this->_code = 1020;
            $ProduitLib = new ProduitLib($this->_conn);
            $produit = $ProduitLib->getAllActif();

            if (empty($produit)) {
                throw new \Exception("");
            }

            self::$SUCCESS_RESPONSE["response"] = $produit;

            return self::$SUCCESS_RESPONSE;
        } catch (\Exception $th) {
            self::$ERROR_RESPONSE["response"] = $th->getMessage();
            self::$ERROR_RESPONSE["code"]     = $this->_code;
            return self::$ERROR_RESPONSE;
        }
    }
}
