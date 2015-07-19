<?php namespace App\Http\Middleware;

use Closure;
use Session;
use Log;


class ValidatePayload {

    /**
     * Handle an incoming request.
     *
     * Validate that the payload is well-formed and includes the appropriate required fields.
     * Also validate any specific fields that have format requirements, like IP addresses.
     * Use a similar structure to that of MixPanel's https://mixpanel.com/help/reference/http
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::info('ValidatePayload start');
        if (!Session::has('payload')) {
            return response("Payload error in ValidatePayload", 400);
        }
        $payload = Session::get('payload');
        if (!$payload) {
            return response("Payload error (2) in ValidatePayload", 400);
        }
        $error_msg = "";
        if (!isset($payload['event']) || empty($payload['event'])) {
            // event is a required string
            $error_msg = "no event specified";
        }
        elseif (!isset($payload['properties']) || !is_array($payload['properties'])) {
            // properties is a required array
            $error_msg = "no properties specified or not an array";
        }
        elseif (!isset($payload['properties']['token']) || empty($payload['properties']['token'])) {
            // token is an important value
            $error_msg = "no token value found.";
        }
        //Log::info("ValidatePayload, error_msg='$error_msg'");
        if (!empty($error_msg)) {
            return response("Payload validation failed: $error_msg", 400);
        }
        // Here the payload has passed validation.
        // Set the token as an input value for later handling.
        Session::put('token', $payload['properties']['token']);
        // Event name will be resolved to an ID later
        Session::put('event_name', $payload['event']);
        // Create a list of all the non-token properties
        $properties = $payload['properties'];
        unset($properties['token']);
        Session::put('properties', $properties);
        // Create a list of all of the meta / internal properties
        $meta = array();
        if (isset($payload['meta'])) {
            $meta = $payload['meta'];
        }
        $meta['ts_received'] = time();
        $meta['sender_ip'] = $request->ip();
        if (!isset($payload['timestamp'])) {
            // default event timestamp to receive time
            $payload['timestamp'] = $meta['ts_received'];
            Session::put('payload', $payload); // re-save.
        }
        Session::put('meta_properties', $meta);
        // on to the next stage
        Log::info("ValidatePayload, session:\n".print_r(Session::all(), true));
        return $next($request);
    }

}
