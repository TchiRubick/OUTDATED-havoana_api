<?php

namespace App\Library;

use App\Utilisateur;
use Illuminate\Support\Facades\Log;

class UserLib
{
    public function __construct($conn = null)
    {
        if ($conn) {
            Utilisateur::setInfosDb($conn);
        }
    }

    public function getByLoginPassword($login, $password)
    {
        $result = null;

        try {
            $user = Utilisateur::join("sys_role", "sys_utilisateur.utl_role", "=", "sys_role.rl_idexterne")
                ->select("utl_idexterne", "rl_code")
                ->where("utl_login", "=", $login)
                ->where("utl_password", "=", md5($login.$password))
                ->where("utl_status", "=", "1")
                ->where("rl_code", "<>", "AGS")
                ->take(1)
                ->get();

            if (count($user) > 0) {
                $result = $user[0];
            }
        } catch (\Throwable $th) {
            Log::error("UserLib - getByLoginPassword() => ", [$th->getMessage()]);
        }

        return $result;
    }

    public function getRoleByLogin($login)
    {
        $result = null;

        try {
            $user = Utilisateur::join("sys_role", "sys_utilisateur.utl_role", "=", "sys_role.rl_idexterne")
                ->select("rl_code", "rl_libelle")
                ->where("utl_login", "=", $login)
                ->take(1)
                ->get();

            if (count($user) > 0) {
                $result = $user[0];
            }
        } catch (\Throwable $th) {
            Log::error("UserLib - getRoleByLogin() => ", [$th->getMessage()]);
        }

        return $result;
    }
}
