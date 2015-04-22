<?php namespace App\Http\Middleware;

use Closure;
use Session;
use Log;


class DecodeData {

    /**
     * Handle an incoming request.
     *
     * once past this Middleware, the 'payload' request value should hold a assoc array of the request data
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if data is set, decode it and set data_json
        Log::info('DecodeData start');
        if ($request->has('data')) {
            $json = base64_decode($request->input('data'));
            if (FALSE === $json) {
                return response("Unable to decode data", 401);
            }
            $request->merge(['data_json' => $json]);
        }
        $payload = json_decode($request->input('data_json'), true);
        if (FALSE === $payload) {
            return response("Unable to decode data json", 401);
        }
        // $payload is an associative array, not a StdClass!
        //$request->merge(['payload' => $payload]);
        Session::put('payload', $payload);
        Log::info("DecodeData done, payload: \n".print_r($payload,true));
        return $next($request);
    }

}
