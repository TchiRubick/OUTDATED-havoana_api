<?php

namespace App\Library;

use App\Produit;
use Illuminate\Support\Facades\Log;

class ProduitLib
{
    public function __construct($conn = null)
    {
        if ($conn) {
            Produit::setInfosDb($conn);
        }
    }

    public function getAllActif($magasin)
    {
        $result = [];

        try {
            $produit = Produit::select("prd_nom", "prd_codebarre", "magst_quantite", "magst_prix")
                ->join('tr_magstock', 'prd_idexterne', 'magst_prdid')
                ->where("magst_quantite", ">", 0)
                ->where("magst_mag", "=", $magasin)
                ->get();

            if (count($produit) > 0) {
                $result = $produit;
            }
        } catch (\Exception $th) {
            Log::error("ProduitLib - getAllActif() => ", [$th->getMessage()]);
        }

        return $result;
    }

    public function decrementProduitByCodebarre(array $produit, $magasin)
    {
        $result = true;
        try {
            foreach ($produit as $prd) {
                Produit::where("prd_codebarre", "=", $prd['prd_codebarre'])
                    ->where('magst_mag', '=', $magasin)
                    ->join('tr_magstock', 'prd_idexterne', 'magst_prdid')
                    ->decrement("magst_quantite", $prd['prd_quantite']);
            }
        } catch (\Exception $th) {
            Log::error("ProduitLib - decrementProduitByCodebarre() => ", [$th->getMessage()]);
            $result = false;
        }

        return $result;
    }

    public function getProduitByCb($codebarre) 
    {
        try {
            $produit = Produit::select("prd_idexterne")
                    ->where("prd_codebarre", "=", $codebarre)
                    ->take(1)
                    ->get();

            return isset($produit[0]) ? $produit[0] : [];
        } catch (\Exception $th) {
            Log::error("ProduitLib - getProduitByCb() => ", [$th->getMessage()]);
            return [];
        }
    }
}
