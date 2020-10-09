<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\Guid;
use App\Library\DeviceTransacLib;
use App\Library\ProduitLib;
use App\Library\VenteLib;

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
            $vente          = [];
            $Guid           = new Guid();
            $guidTransac    = $Guid->generatGuid();

            $ProduitLib = new ProduitLib($this->_conn);

            foreach ($panier as $pan) {
                $montantTotal += $pan['article']['magst_prix'] * $pan['quantite'];
                $detail[] = array(
                    "tscd_idexterne"    => $Guid->generatGuid(),
                    "tscd_produit"      => $pan['article']['prd_codebarre'],
                    "tscd_quantite"     => $pan['quantite'],
                    "tscd_montant"      => $pan['article']['magst_prix'] * $pan['quantite'],
                    "tscd_datecrea"     => date('Y-m-d H:i:s'),
                    "tscd_transac"      => $guidTransac
                );

                $produit[] = array(
                    "prd_codebarre" => $pan['article']['prd_codebarre'],
                    "prd_quantite"  => $pan['quantite'],
                );

                $prd = $ProduitLib->getProduitByCb($pan['article']['prd_codebarre']);

                $vente[] = array(
                    "vnt_mag"       => $this->_session->MAG,
                    "vnt_caisse"    => $this->_session->MACHINE,
                    "vnt_userid"    => $this->_session->ID,
                    "vnt_date"      => date('Y-m-d H:i:s'),
                    "vnt_update"    => date('Y-m-d H:i:s'),
                    "vnt_prix"      => $pan['article']['magst_prix'],
                    "vnt_quantite"  => $pan['quantite'],
                    "vnt_prdid"     => $prd['prd_idexterne'],
                    "vnt_type"      => 'VNT'
                );
            }

            $this->_code = 1140;
            $DeviceTransacLib = new DeviceTransacLib($this->_conn);
            $isInserted = $DeviceTransacLib->setSellTransac($guidTransac, $montantTotal, $this->_session->ID, $this->_session->MACHINE);

            if (!$isInserted) throw new \Exception("Erreur sauvegarde transaction");

            $objVente = new VenteLib($this->_conn);

            foreach ($vente as $vnt) {
                $isInserted = $objVente->venteSet($vnt);
                if (!$isInserted) throw new \Exception("Erreur sauvegarde vente");
            }

            $this->_code = 1150;
            $isInserted = $DeviceTransacLib->setSellTransacDetail($detail);

            if (!$isInserted) {
                $DeviceTransacLib->updateFailedAuth($guidTransac, $this->_session->MACHINE);
                throw new \Exception("Erreur sauvegarde detail");
            }

            $this->_code = 1160;
            $isInserted = $ProduitLib->decrementProduitByCodebarre($produit, $this->_session->MAG);

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
