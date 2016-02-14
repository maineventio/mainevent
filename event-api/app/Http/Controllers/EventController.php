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
use Queue;
use App\Commands\NewEvent;
use AWS;

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
        Log::info('event_new, Session:\n'.print_r(Session::all(),true));

        // Update the meta for the enqueue time
        $meta = Session::get('meta_properties');
        $meta['ts_queued'] = time();

        // Push onto SQS queue
        $project = Session::get('project');
        $event = Session::get('event');
        $payload = [
            'project_id' => $project->id,
            'event' => $event,
            'event_name' => Session::get('event_name'),
            'properties' => Session::get('properties'),
            'meta' => $meta,
        ];
        Log::info('event_new, pushing payload:\n'.print_r($payload,true));

        /*
         * This Queue::push is if we expect the message to be handled by other Laravel/Lumen code,
         * for example an early iteration pushed the event to the queue, and there was a commandline
         * pump that moved them from the queue to DynamoDB.
         * Current (Feb 2016) iteration is to have LogStash pulling from the queue, so we want to push our
         * JSON, not the serialized NewEvent object.
         */
        /*
        // TODO: error check?
        Queue::push(new NewEvent($payload));
        */
        $sqs = AWS::createClient('sqs');
        $sqs->sendMessage([
            'QueueUrl' => env('SQS_URL'),
            'MessageBody' => json_encode($payload),
        ]);

        Log::info('event_new pushed to queue');

        // Disable Kinesis for now, this is experimental (as in, compare against SQS or allow add'l AWS integrations).
        /*
        $kinesis = AWS::createClient('kinesis');
        $kinesis->putRecord([
            'StreamName' => 'mainevent-incoming',
            'PartitionKey' => $event->id,
            'Data' => $payload
        ]);
        Log::info('event_new pushed to Kinesis stream');
        */

        Log::info('event_new done');
        // Say thanks!
        return response(
            "<< Event recorded >>\n\nThank you, thank you, thank you, you're far too kind\nHold your applause, this is your song not mines.'\n -- Jay Z: http://genius.com/Jay-z-thank-you-lyrics",
            200);
    }

    /**
     * PUMP AN EVENT FROM SQS TO DYNAMODB
     * Handle a single event via HTTP
     * Triggered by the EBS Worker Tier queue daemon
     * http://docs.aws.amazon.com/elasticbeanstalk/latest/dg/using-features-managing-env-tiers.html
     */
    public function event_pump(Request $request) {

        /**
         * HEY YOU
         * This is for using the worker tier daemon, which turns SQS messages into HTTP hits.
         * It would need a route, but doesn't have one.
         * I'm moving the interesting code to the NewEvent::handle(), so that we can
         * also use Laravel's queue handling
         * http://laravel.com/docs/5.0/queues#running-the-queue-listener
         */


        /**
         * Event IDs
         * We will default to using the SQS message ID as the event ID
         * https://aws.amazon.com/articles/Amazon-SQS/1343#05
         *
         * Look at using Conditional Writes, we dont want to overwrite any event record
         * which will happen if we PutItem on the same ID.
         * https://aws.amazon.com/articles/Amazon-SQS/1343#05
         * Condition expression: attribute_not_exists(RelatedItems)
         * http://docs.aws.amazon.com/amazondynamodb/latest/developerguide/Expressions.Modifying.html#Expressions.Modifying.ConditionalWrites
         *
         */

        /*
        $msg_id = $request->header('X-Aws-Sqsd-Msgid');
        $post_data = $request->getContent(); // http://www.codingswag.com/get-raw-post-data-in-laravel/


        */
    }


}