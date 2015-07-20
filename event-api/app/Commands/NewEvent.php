<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use AWS;
use Aws\DynamoDb\Marshaler;
use App;
use Log;

class NewEvent extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;
    protected $payload;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($payload)
	{
        $this->payload = $payload;
		//
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
        if (!isset($this->payload['timestamp'])) {
	    $this->payload['timestamp'] = $this->payload['meta']['ts_received'];
	}
        Log::info("NewEvent::handle: pumping event to DB store:\n".print_r($this->payload,true));
        // use the AWS Laravel thing
        // http://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-dynamodb.html#adding-items
        $dynamodb = AWS::createClient('dynamodb');
        $marshaler = new Marshaler();
        $putArgs = array(
            'TableName' => 'events',
            'Item' => $marshaler->marshalJson(json_encode($this->payload))
        );
        Log::info("NewEvent::handle: about to putItem:\n".print_r($putArgs,true));
        $result = $dynamodb->putItem($putArgs);
        Log::info("NewEvent::handle: putItem result:\n".print_r($result,true));
    }
}
