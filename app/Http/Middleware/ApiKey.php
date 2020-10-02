<?php

namespace App\Http\Middleware;

use Closure;

class ApiKey
{

    public static $ERR_AUTHENTIFICATION = array(
        "error" => 1,
        "code" => 1001,
        "response" => ''
    );

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
            if (!$request->header('X-Token')) {
                throw new \Exception('Not Authorized');
            }

            $xtoken = $request->header("X-Token");

            if ($xtoken != APP_KEY) {
                throw new \Exception('Not Authorized');
            }

            return $next($request);
        } catch (\Exception $th) {
            self::$ERR_AUTHENTIFICATION["response"] = $th->getMessage();
            return self::$ERR_AUTHENTIFICATION;
        }
    }
}
