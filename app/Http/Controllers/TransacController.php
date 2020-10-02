<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\Guid;
use App\Library\DeviceTransacLib;
use App\Library\ProduitLib;

use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Logique des code erreur.
 * digit 1   : Degré de l'erreur (1 -> 9)
 * digit 2   : Niveau de blocage (1 -> 9)
 * digit 3-4 : Etape dans le code (10 -> 99)
 */

class TransacController extends BaseController
{
    private $_code = 0;
    private $_conn;
    private $_session;

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

    public function setTransacSell(Request $request)
    {

        try {
            $this->_code = 1010;
            $this->validate($request, [
                'panier'   => 'required',
            ]);

            $this->_conn = $request->get('connection');
            $this->_session = $request->get('session');

            $this->_code = 1020;
            $panier = $request->input("panier");

            $this->_code = 1030;

            $montantTotal   = 0;
            $detail         = [];
            $produit        = [];
            $Guid           = new Guid();
            $guidTransac    = $Guid->generatGuid();

            foreach ($panier as $pan) {
                $montantTotal += $pan['article']['prd_prixvente'] * $pan['quantite'];
                $detail[] = array(
                    "tscd_idexterne"    => $Guid->generatGuid(),
                    "tscd_produit"      => $pan['article']['prd_codebarre'],
                    "tscd_quantite"     => $pan['quantite'],
                    "tscd_montant"      => $pan['article']['prd_prixvente'] * $pan['quantite'],
                    "tscd_datecrea"     => date('Y-m-d H:i:s'),
                    "tscd_transac"      => $guidTransac
                );

                $produit[] = array(
                    "prd_codebarre" => $pan['article']['prd_codebarre'],
                    "prd_quantite"  => $pan['quantite'],
                );
            }

            $this->_code = 1140;
            $DeviceTransacLib = new DeviceTransacLib($this->_conn);
            $isInserted = $DeviceTransacLib->setSellTransac($guidTransac, $montantTotal, $this->_session->ID, $this->_session->MACHINE);

            if (!$isInserted) throw new \Exception("Erreur sauvegarde transaction");

            $this->_code = 1150;
            $isInserted = $DeviceTransacLib->setSellTransacDetail($detail);

            if (!$isInserted) {
                $DeviceTransacLib->updateFailedAuth($guidTransac, $this->_session->MACHINE);
                throw new \Exception("Erreur sauvegarde detail");
            }

            $this->_code = 1160;
            $ProduitLib = new ProduitLib($this->_conn);
            $isInserted = $ProduitLib->decrementProduitByCodebarre($produit);

            if (!$isInserted) {
                $DeviceTransacLib->updateFailedAuth($guidTransac, $this->_session->MACHINE);
                throw new \Exception("Erreur mise à jour stock");
            }

            return self::$SUCCESS_RESPONSE;
        } catch (\Exception  $th) {
            self::$ERROR_RESPONSE["response"] = $th->getMessage();
            self::$ERROR_RESPONSE["code"]     = $this->_code;
            return self::$ERROR_RESPONSE;
        }
    }
}
