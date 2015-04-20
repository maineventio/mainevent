<?php namespace App\Http\Controllers;
/**
 * Created by PhpStorm.
 * User: jmagnuss
 * Date: 15-04-16
 * Time: 4:28 PM
 */

use App\Http\Controllers\Controller;
use Session;
use Log;

// Useful http status codes for REST: http://www.restapitutorial.com/httpstatuscodes.html
class EventController extends Controller
{
    /**
     * Handle an incoming track event
     *
     */
    public function event_new() {
        if (!Session::has('project')) {
            return response("Validation error, broken session", 400);
        }
        // Just to see what's been processed and set...
        //Log::info('event_new, Session:\n'.print_r(Session::all(),true));

        // Push onto SQS queue
        $project = Session::get('project');
        $payload = [
            'project_id' => $project->id,
            'event_name' => Session::get('event_name'),
            'properties' => Session::get('properties')
        ];
        Log::info('event_new, pushing payload:\n'.print_r($payload,true));
        Queue::push(new NewEvent($payload));
    }


}