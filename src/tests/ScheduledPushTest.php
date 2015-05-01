<?php
/**
 * @copyright 2013 Urban Airship and Contributors
 */

use UrbanAirship\Push as P;

class TestScheduledPushRequest extends PHPUnit_Framework_TestCase
{
    public function testSimpleScheduledPushRequest()
    {
        $response = new StdClass();
        $response->code = 201;
        $response->raw_headers = array();
        $response->raw_body = "{\"schedule_urls\": [\"https://go.urbanairship.com/api/schedules/41742a47-bd36-4a0e-8ce2-866cd8f3b1b5\"]}";

        $airship = $this->getMock('UrbanAirship\Airship', array('request', 'buildUrl'), array('appkey', 'mastersecret'));
        $airship->expects($this->any())
             ->method('request')
             ->will($this->returnValue($response));

        $push = new P\PushRequest($airship);
        $push = $push
            ->setAudience(P\all)
            ->setNotification(P\notification("Hello"))
            ->setDeviceTypes(P\all)
            ->setOptions(array());
        $sched = new P\ScheduledPushRequest($airship);
        $sched = $sched
            ->setName("A schedule")
            ->setPush($push)
            ->setSchedule(P\scheduledTime(time() + 15));

        $response = $sched->send();
    }
}

