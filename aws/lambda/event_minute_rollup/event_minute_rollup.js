//console.log('Loading function');
var async = require('async');  
var aws = require('aws-sdk');
var dynamo = new aws.DynamoDB();

function rollupItem(record, rollupItemCallback) {
    if (record.eventName != 'INSERT') {
      console.log('SKIP: not an insert: ', JSON.stringify(record, null, 2));
      rollupItemCallback('Not an INSERT', null);
      return;
    }
    var item = record.dynamodb;
    console.log('rollupItem: %j', item);
    async.parallel([
        // Event count incrementer
        function(callback) {
            console.log("parallel: summary start");
            var itemTimestamp = item.Keys.timestamp.N;
            var eventHash = item.Keys.event_hash.S; // format YYYYMMDD.eventID
            console.log('rollupItem itemTimestamp='+itemTimestamp+' eventHash='+eventHash);
            var dt = new Date(itemTimestamp*1000);
            //var minute = dt.getHours().dt.getMinutes();
            var minute = dt.getHours()*60 + dt.getMinutes();
            // clear seconds to get a good minute timestamp
            dt.setSeconds(0);
            dt.setMilliseconds(0);
            var minuteTime = dt.getTime() / 1000;
            // TODO: Note that the :num=1 value is where we could do a better job of incrementer attributes.
            var updateParams = {
                TableName: 'event_minute',
                Key: {
                    'event_hash': { S: eventHash },
                    'minute': { N: minute.toString() }
                },
                UpdateExpression: 'ADD seen :num SET dt = :minuteTime',
                ExpressionAttributeValues: {
                    ':num': { N: '1' },
                    ':minuteTime': { N: minuteTime.toString() }
                },
                ReturnValues: 'ALL_NEW'
            };
            dynamo.updateItem(updateParams, function (err,data) {
                if (err) {
                    console.log('error','updateItem failed: '+err);
                    return callback(err);
                }
                else {
                    console.log('success: '+JSON.stringify(data, null, '  '));
                    callback();
                }        
            });
        },
        // Log item details to CloudWatch for DataPipeline
        function(callback) {
            var rec = item.NewImage; // the DynamoDB item
            //console.log('rec: %j', rec);
            var mainData = {};
            mainData.timestamp = rec.timestamp.N;
            mainData.distinct_id = rec.properties.M.distinct_id.S;
            mainData.project_id = rec.project_id.N;
            mainData.event_hash = rec.event_hash.S;
            mainData.event_id = rec.event.M.id.N;
            mainData.event_name = rec.event.M.name.S;
            mainData.event_hidden = rec.event.M.hidden.N;
            mainData.sender_ip = rec.meta.M.sender_ip.S;
            mainData.properties = {};
            for (var prop_name in rec.properties.M) {
                if (prop_name == 'distinct_id') {
                    continue;
                }
                mainData.properties[prop_name] = rec.properties.M[prop_name].S;
            }
            console.log('EVENTREC: %j', mainData);
            callback();
        },
        ], function(err) { // called after all parallel funcs complete
            console.log('parallel final, err='+err);
            rollupItemCallback(err, item);
        });
    }

exports.handler = function(event, context) {
    //console.log('Received event:', JSON.stringify(event, null, 2));
    console.log('handler begin, num events: '+event.Records.length);
    async.map(event.Records, rollupItem, function(err, results) {
        console.log('async map done, err='+err);
        console.log('num events: '+event.Records.length);
        if (err) {
            context.fail(err);
        }
        else {
            context.succeed("Successfully processed " + event.Records.length + " records.");
        }
    });
};
