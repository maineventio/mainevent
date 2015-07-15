<?php
/**
 * Created by PhpStorm.
 * User: jmagnuss
 * Date: 15-05-22
 * Time: 10:25 PM
 */

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class EventQueueController extends Controller {

    /**
     * Handle a single event via HTTP
     * Triggered by the EBS Worker Tier queue daemon
     * http://docs.aws.amazon.com/elasticbeanstalk/latest/dg/using-features-managing-env-tiers.html
     */
    public function pop(Request $request) {

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
        $msg_id = $request->header('X-Aws-Sqsd-Msgid');
        $post_data = $request->getContent(); // http://www.codingswag.com/get-raw-post-data-in-laravel/

        // use the AWS Laravel thing
        // http://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-dynamodb.html#adding-items
        $dynamodb = App::make('aws')->get('dynamodb');
        $dynamodb->putItem(); // TODO



    }

} 