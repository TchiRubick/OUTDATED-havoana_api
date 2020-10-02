<?php

namespace App\Http\Middleware;

use Closure;
use App\Library\Decrypteur;
use App\Library\SocieteLib;

class TokenRecognition
{

    public static $ERR_AUTHENTIFICATION = array(
        "error" => 1,
        "code" => 1001,
        "response" => ''
    );

    private $years;
    private $months;
    private $days;
    private $hours;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $this->_code = 1002;
            if (!$request->header('Token')) {
                throw new \Exception('Not Authorized');
            }

            $decrypteur = new Decrypteur;
            $decrypteur->parse($request->header('Token'));

            $this->_code = 1003;
            if (!$this->isDataValid($decrypteur->DATE)) {
                throw new \Exception('Session expired');
            }

            $this->_code = 1010;
            $socConnection = (new SocieteLib)->authenticateByIde($decrypteur->IDS);

            if (!$socConnection) {
                throw new \Exception("Societe not recognized");
            }

            $request->attributes->add(["session" => $decrypteur]);
            $request->attributes->add(["connection" => $socConnection]);

            return $next($request);
        } catch (\Exception $th) {
            self::$ERR_AUTHENTIFICATION["response"] = $th->getMessage();
            return self::$ERR_AUTHENTIFICATION;
        }
    }

    private function isDataValid($date)
    {
        // Calculate the date difference if allowed token duration
        $date_token = strtotime($date);
        $date_req = strtotime(date('Y-m-d H:i:s'));

        $diff = abs($date_req - $date_token);

        $this->evaluator($diff);

        return $this->checker();
    }

    private function evaluator($diff)
    {
        // To get the year divide the resultant date into
        // total seconds in a year (365*60*60*24)
        $this->years = floor($diff / (365 * 60 * 60 * 24));


        // To get the month, subtract it with this->years and
        // divide the resultant date into
        // total seconds in a month (30*60*60*24)
        $this->months = floor(($diff - $this->years * 365 * 60 * 60 * 24)
            / (30 * 60 * 60 * 24));


        // To get the day, subtract it with this->years and
        // this->months and divide the resultant date into
        // total seconds in a this->days (60*60*24)
        $this->days = floor(($diff - $this->years * 365 * 60 * 60 * 24 -
            $this->months * 30 * 60 * 60 * 24) / (60 * 60 * 24));


        // To get the hour, subtract it with this->years,
        // this->months & seconds and divide the resultant
        // date into total seconds in a this->hours (60*60)
        $this->hours = floor(($diff - $this->years * 365 * 60 * 60 * 24
            - $this->months * 30 * 60 * 60 * 24 - $this->days * 60 * 60 * 24)
            / (60 * 60));
    }

    private function checker()
    {
        if ($this->years > 1) return false;
        if ($this->months > 1) return false;
        if ($this->days > 1) return false;
        if ($this->hours > 10) return false;

        return true;
    }
}
