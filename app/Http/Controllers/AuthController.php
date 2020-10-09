<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Library\AuthLib;
use App\Library\SocieteLib;
use App\Library\DeviceTransacLib;
use App\Library\UserLib;
use App\Library\MaguserdeviceLib;

/**
 * Logique des code erreur.
 * digit 1   : Degré de l'erreur (1 -> 9)
 * digit 2   : Niveau de blocage (1 -> 9)
 * digit 3-4 : Etape dans le code (10 -> 99)
 */


class AuthController extends BaseController
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
    }

    public function authentification(Request $request)
    {
        try {
            $this->_code = 1000;

            $this->validate($request, [
                'societe'   => 'required|string',
                'login'     => 'required|string',
                'password'  => 'required|string',
                'machine'   => 'required|string'
            ]);

            // Vérifie si la société est éxistant, récupère la configuration client si Oui.
            $this->_code = 1010;
            $this->_conn = (new SocieteLib)->authenticateByCode($request->input('societe'));

            if (!$this->_conn) {
                throw new \Exception("Societe not recognized");
            }

            // Verifie si le device est authorisé et ecrit la transaction.
            $this->_code = 1020;
            $DeviceTransacLib       = new DeviceTransacLib($this->_conn);
            $b_deviceExist          = $DeviceTransacLib->isDeviceExist($request->input('machine'));

            if (!$b_deviceExist) {
                $DeviceTransacLib->setNewDevice($request->input('machine'));
                throw new \Exception("Device not recognized, please tell your admin to register your device");
            }

            // Vérifie si login et le mot de passe correspond à un utilisateur
            $this->_code = 1030;
            $UserLib    = new UserLib($this->_conn);
            $user       = $UserLib->getByLoginPassword($request->input('login'), $request->input('password'));
            
            if (!$user) {
                $DeviceTransacLib->updateFailedAuth($DeviceTransacLib->guidTrans);
                throw new \Exception("User not found");
            }

            $objMag = new MaguserdeviceLib($this->_conn);
            $a_magU = $objMag->getMagasinConnection(md5($request->input('machine')), $user['utl_idexterne']);
            
            if (empty($a_magU)) {
                throw new \Exception("Store not set");
            }

            if ($user["rl_code"] !== "SUP") {
                $this->_code = 1040;

                if (!$DeviceTransacLib->isDeviceAllowedOnUser(md5($request->input('machine')), $user['utl_idexterne'])) {
                    $DeviceTransacLib->updateFailedAuth($DeviceTransacLib->guidTrans);
                    throw new \Exception("Device not recognized, please tell your admin to register your device");
                }
            }

            $this->_code = 1050;
            self::$SUCCESS_RESPONSE["response"] = (new AuthLib)->authResponse($user, $this->_conn, md5($request->input('machine')), $a_magU["magudvc_mag"]);
            return self::$SUCCESS_RESPONSE;
        } catch (\Exception $th) {
            self::$ERROR_RESPONSE["response"] = $th->getMessage();
            self::$ERROR_RESPONSE["code"]     = $this->_code;
            return self::$ERROR_RESPONSE;
        }
    }
}
