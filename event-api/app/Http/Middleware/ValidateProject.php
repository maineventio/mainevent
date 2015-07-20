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
        $cacheMinutes = 15;
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
                Cache::add($token_cachekey, $project, $cacheMinutes);
            }
        }
        if (!isset($project)) {
            return response("Unable to locate project for specified token", 401);
        }
        // Project is a database row record.  See http://laravel.com/docs/5.0/queries
        Session::put('project', $project);
        Log::info("ValidateProject, project:\n".print_r($project,true));

        // Same approach for the event - check cache, ensure exists in DB.
        // event ID will be used in our data store hash key.
        $eventTable = 'project_events';
        $eventName = Session::get('event_name');
        $event_cachekey = "event-token-".$project->id."-".$eventName;
        $event = NULL;
        if (Cache::has($event_cachekey)) {
            $event = Cache::get($event_cachekey);
        }
        else {
            $event = DB::table($eventTable)->where('project_id', $project->id)->where('name',$eventName)->first();
            if (!isset($event)) {
                // record the new event
                DB::table($eventTable)->insert(
                    ['project_id' => $project->id, 'name' => $eventName, 'first_seen' => time()]
                );
                // re-fetch
                $event = DB::table($eventTable)->where('project_id', $project->id)->where('name',$eventName)->first();
            }
            if (isset($event)) {
                // cache the event record
                Cache::add($event_cachekey, $event, $cacheMinutes);
            }
        }
        if (!isset($event)) {
            return response("Error fetching/creating event record for this event name", 500);
        }
        Session::put('event', $event);
        Log::info("ValidateProject, event:\n".print_r($event,true));

        // On to the next one!
        return $next($request);
    }

}
