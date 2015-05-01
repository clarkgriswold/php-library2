<?php
/**
 * @copyright 2013 Urban Airship and Contributors
 */

use UrbanAirship\Push as P;
use UrbanAirship\Push\PushResponse;

class TestPushRequest extends PHPUnit_Framework_TestCase
{
    public function testSimplePushRequest()
    {
        $response              = new StdClass();
        $response->code        = 202;
        $response->raw_headers = array();
        $response->raw_body    = "{\"push_ids\": [\"41742a47-bd36-4a0e-8ce2-866cd8f3b1b5\"]}";

        $airship = $this->getMock('UrbanAirship\Airship', array('request', 'buildUrl'), array('appkey', 'mastersecret'));

        $airship->expects($this->any())
             ->method('request')
             ->will($this->returnValue($response));

        $push = new P\PushRequest($airship);
        $push
            ->setAudience(P\all)
            ->setNotification(P\notification("Hello"))
            ->setDeviceTypes(P\all)
            ->setOptions(array());

        $this->assertEquals(new PushResponse($response), $push->send());
    }
}
