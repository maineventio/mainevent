<?php namespace App\Http\Middleware;

use \palanik\lumen\Middleware\LumenCors;

class CORS extends LumenCors {

    public function __construct() {
        $this->settings['allowCredentials'] = true;
        $this->settings['origin'] = function($headerOrigin) {
            return (empty($headerOrigin)) ? "*" : $headerOrigin;
        };
    }

    /*
     * Override, checks Origin sent by requesting browser and uses that
     */
    protected function disable_setOrigin($req, $rsp) {
        $origin = "*";
        if (!empty($req->header("Origin"))) {
            $origin = $req->header("Origin");
        }
        $rsp->header('Access-Control-Allow-Origin', $origin);
    }

}
