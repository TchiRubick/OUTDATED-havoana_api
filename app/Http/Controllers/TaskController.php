<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Lumen\Routing\Controller as BaseController;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use App\Library\SocieteLib;
use App\Library\DeviceTransacLib;
use App\Library\UserLib;

/**
 * Logique des code erreur.
 * digit 1   : Degré de l'erreur (1 -> 9)
 * digit 2   : Niveau de blocage (1 -> 9)
 * digit 3-4 : Etape dans le code (10 -> 99)
 */

class TaskController extends BaseController
{
    private $_code = 0;

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

    public function sendMailNewUser(Request $request)
    {
        $this->_code = 1010;
        $mail = new PHPMailer(true);

		try {

            if (!$request->has('email')) throw new \Exception('email requis');
            if (!$request->has('login')) throw new \Exception('login requis');
            if (!$request->has('password')) throw new \Exception('password requis');
            if (!$request->has('societe')) throw new \Exception('societe requis');
            if (!$request->has('numtransac')) throw new \Exception('numtransac requis');

            $s_email        = $request->input('email');
            $s_login        = $request->input('login');
            $s_password     = $request->input('password');
            $s_societe      = $request->input('societe');
            $s_numtransac   = $request->input('numtransac');

            $this->_code = 1010;
            $this->_conn = (new SocieteLib)->authenticateByCode($s_societe);

            if (!$this->_conn) throw new \Exception("Societe not recognized");

            $_objTransac = new DeviceTransacLib($this->_conn);
            $_objUser    = new UserLib($this->_conn);

            $a_role      = $_objUser->getRoleByLogin($s_login);

            if (!$a_role) {
                throw new \Exception("User not found");
            }
			//Server settings
			$mail->isSMTP();
			$mail->SMTPDebug  = FALSE;
			$mail->Host       = SMTP_HOST;
			$mail->SMTPAuth   = true;
			$mail->Username   = SMTP_USER;
			$mail->Password   = SMTP_PASS;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port       = SMTP_PORT;

			//Recipients
			$mail->setFrom(SMTP_FROM, SMTP_NAME);
			$mail->addAddress($s_email);

			// Content
			$mail->isHTML(true);
			$mail->Subject = "Votre compte Havoana !";
            $mail->Body    = "<p>Votre adresse vient d'être ajouter à notre site ou bien vos coordonées de connexion ont été mise à jour par votre administrateur.</p>"
                . "<p>Ci-dessous vos identifiant de connexion à notre platforme suivant votre type de compte.</p>"
				. "<ul>"
                . "<li>Votre code societe: <b>" . $s_societe . "</b></li>"
				. "<li>Votre login: <b>" . $s_login . "</b></li>"
				. "<li>Votre mot de passe: <b>" . $s_password . "</b></li>"
				. "<li>Type du compte: <b>" . $a_role["rl_libelle"] . "</b></li>"
				. "<li>Lien pour accéder à l'ERP: <a href='" . ERP_URL . "'>erp.havoana.net</a></li>"
				. "<li>Lien pour accéder au POS: <a href='" . POS_URL . "'>cashier.havoana.net</a> </li>"
                . "</ul>"
                . "<p>Cependant, il faudrait vérifier auprès de votre administrateur si le compte est actif.</p>"
                . "<p style='color:red;'>NB: Un compte <i>Agent de caisse</i> doit avoir sont appareil autorisé à utiliser l'application POS. <br/> Authentifiez vous une(1) fois puis notifier votre administrateur pour autoriser l'appareil;</p>";
			$mail->AltBody = 'Ce mail ne prend pas en charge vos réponse';

            if(!$mail->send()) {
                $_objTransac->updateSentFailed($s_numtransac);
                throw new \Exception('Erreur envoie mail');
            }

            $_objTransac->updateSentSuccess($s_numtransac);

            return self::$SUCCESS_RESPONSE;
		} catch (Exception $e) {
            Log::error("TaskController - sendMailNewUser() => ", [$mail->ErrorInfo, $e->getMessage()]);
            self::$ERROR_RESPONSE["response"] = array($mail->ErrorInfo, $e->getMessage());
            self::$ERROR_RESPONSE["code"]     = $this->_code;
            return self::$ERROR_RESPONSE;
		}
    }
}
