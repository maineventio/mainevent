<?php namespace App\Http\Middleware;

use Closure;
use Session;
use Log;
use Cache;
use DB;


class ValidateProject {

    /**
     * Handle an incoming request.
     *
     * After the payload has been validated and the session has a known TOKEN, find the
     * associated project and make sure it exists, and is allowed to send data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::info('ValidateProject start');
        if (!Session::has('token')) {
            return response("Token error in ValidateProject", 400);
        }
        // Find the project record.
        $token = Session::get('token');
        $token_cachekey = "project-token-".$token;
        $project = NULL;
        if (Cache::has($token_cachekey)) {
            $project = Cache::get($token_cachekey);
        }
        else {
            // Load the project from the main shared db.
            // Doing it without Eloquent, so we stay leaner here and so we don't have conflicting models or
            // migrations.
            $project = DB::table('projects')->where('token', $token)->first();
            if (isset($project)) {
                // Cache the found result
                $minutes = 15;
                Cache::add($token_cachekey, $project, $minutes);
            }
        }
        if (!isset($project)) {
            return response("Unable to locate project for specified token", 401);
        }
        // Project is a database row record.  See http://laravel.com/docs/5.0/queries
        Session::put('project', $project);
        Log::info("ValidateProject, project:\n".print_r($project,true));

        // On to the next one!
        return $next($request);
    }

}
