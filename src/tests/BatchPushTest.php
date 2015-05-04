<?php
/**
 * @copyright 2015 Urban Airship and Contributors
 */

use UrbanAirship\Push as P;
use UrbanAirship\Push\PushResponse;

class TestBatchPushRequest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \UrbanAirship\AirShip
     */
    private $airship;

    /**
     * @var \UrbanAirship\Push\BatchPushRequest
     */
    private $batchPushRequest;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->airship          = $this->getMock('UrbanAirship\Airship', array(), array('appkey', 'mastersecret'));
        $this->batchPushRequest = new P\BatchPushRequest($this->airship);
    }

    /**
     * Test that BatchPushRequest throws exception when trying to create
     * a new request with anything other than a string.
     *
     * @dataProvider provideDataForTestThatBatchPushRequestEnsuresAStringIdentifier
     * @expectedException UrbanAirship\Push\Exception\BatchPushRequestException
     */
    public function testThatBatchPushRequestEnsuresAStringIdentifier($notificationName)
    {
        $this->batchPushRequest->createNewNotification($notificationName);
    }

    /**
     * Data provider for testThatBatchPushRequestEnsuresAStringIdentifier().
     *
     * @return array
     */
    public function provideDataForTestThatBatchPushRequestEnsuresAStringIdentifier()
    {
        return array(
            array(12345),
            array(123.45),
            array(new StdClass()),
            array(function(){}),
            array(array()),
        );
    }

    /**
     * Test that all setters and pushers validate the existence of the notification name
     * and throw an excpeption if it does not exist.
     *
     * @dataProvider provideDataForTestSettersAndPushersThrowExceptionOnNonExistentNotificationName
     * @expectedException UrbanAirship\Push\Exception\BatchPushRequestException
     */
    public function testSettersAndPushersThrowExceptionOnNonExistentNotificationName($method, $value)
    {
        $this->batchPushRequest->$method('non-existent-notification-name', $value);
    }

    /**
     * Data provider for testSettersAndPushersThrowExceptionOnNonExistentNotificationName().
     */
    public function provideDataForTestSettersAndPushersThrowExceptionOnNonExistentNotificationName()
    {
        return array(
            array('setAudience', 'some-audience'),
            array('setNotification', array()),
            array('setDeviceTypes', array()),
            array('pushDeviceType', 'some-device'),
            array('setMessage', array()),
            array('setOptions', array()),
        );
    }

    /**
     * Test BatchNotificationRequest with a batch of two.
     */
    public function testWithABatchOfTwo()
    {
        $uri             = 'http://domain.com/api/push/';
        $requestResponse = $this->getResponse(array(
            'ok'           => true,
            'operation_id' => '409f1930-f03c-11e4-9461-90e2ba273350',
            'push_ids'     => array(
                '91baebaa-2652-4ccb-bbd0-45305b5ac6ae',
                '0759cb34-f638-4a7e-9f3e-28ae5ed85760',
            ),
        ));

        // First notifiction
        $notificationName = 'some_notification_identifier_1';
        $payloadMap       = array(
            'audienceMap'     => P\alias('some_alias_1'),
            'notificationMap' => array('message' => 'Some message 1'),
            'deviceTypeList'  => array('ios'),
        );

        $this->createNewBatch($notificationName, $payloadMap);

        // Second notification
        $notificationName = 'some_notification_identifier_2';
        $payloadMap       = array(
            'audienceMap'     => P\alias('some_alias_2'),
            'notificationMap' => array('message' => 'Some message 2'),
            'deviceTypeList'  => array('android'),
        );

        $this->createNewBatch($notificationName, $payloadMap);

        $this
            ->airship
            ->expects($this->once())
            ->method('buildUrl')
            ->with('/api/push/')
            ->will($this->returnValue($uri));

        $this
            ->airship
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                json_encode($this->batchPushRequest->getPayload()),
                $uri,
                'application/json',
                3
            )
            ->will($this->returnValue($requestResponse));

        $response = $this->batchPushRequest->send();

        $this->assertEquals(new PushResponse($requestResponse), $response);
        $this->assertEquals($this->getTestWithABatchOfTwoExpectedPayload(), $this->batchPushRequest->getPayload());
    }

    /**
     * Test reseting of the batch payload map.
     */
    public function testResetBatchPayloadMap()
    {
        $notificationName = 'some_notification_identifier';
        $payloadMap       = array(
            'audienceMap'     => P\alias('some_alias'),
            'notificationMap' => array('message' => 'Some message'),
            'deviceTypeList'  => array('android'),
        );

        $this->createNewBatch($notificationName, $payloadMap);

        $this->assertEquals(array(
            array(
                'audience' => array(
                    'alias' => 'some_alias',
                ),
                'notification' => array(
                    'message' => 'Some message',
                ),
                'device_types' => array(
                    'android',
                )
            )),
            $this->batchPushRequest->getPayload()
        );

        $this->batchPushRequest->resetBatchPayloadMap();

        $this->assertEquals(array(), $this->batchPushRequest->getPayload());
    }

    /**
     * Test pushing device types to the payload.
     *
     * @param array $deviceTypeList
     * @param array $expectedResult
     *
     * @dataProvider provideDataForTestPushingDeviceTypes
     */
    public function testPushingDeviceTypes(array $deviceTypeList, array $expectedResult)
    {
        $notificationName = 'some_notification_identifier';

        $this->batchPushRequest->createNewNotification($notificationName);

        foreach($deviceTypeList as $deviceType) {
            $this->batchPushRequest->pushDeviceType($notificationName, $deviceType);
        }

        $this->assertEquals($expectedResult, $this->batchPushRequest->getPayload());
    }

    /**
     * Data provider for testPushingDeviceTypes().
     *
     * @return array
     */
    public function provideDataForTestPushingDeviceTypes()
    {
        return array(
            // Test pushing one type
            array(
                array('ios'),
                array(
                    array(
                        'device_types' => array(
                            'ios',
                        ),
                    ),
                ),
            ),
            // Test pushing two types
            array(
                array(
                    'ios',
                    'android'
                ),
                array(
                    array(
                        'device_types' => array(
                            'ios',
                            'android',
                        ),
                    ),
                ),
            ),
            // Test pushing two of the same resulting in only one.
            array(
                array(
                    'ios',
                    'ios',
                ),
                array(
                    array(
                        'device_types' => array(
                            'ios',
                        ),
                    ),
                ),
            ),
        );
    }

    public function testWithMessagesAndOptions()
    {
        $uri             = 'http://domain.com/api/push/';
        $requestResponse = $this->getResponse(array(
            'ok'           => true,
            'operation_id' => '409f1930-f03c-11e4-9461-90e2ba273350',
            'push_ids'     => array(
                '91baebaa-2652-4ccb-bbd0-45305b5ac6ae',
                '0759cb34-f638-4a7e-9f3e-28ae5ed85760',
            ),
        ));

        // First notifiction
        $notificationName = 'some_notification_identifier_1';
        $payloadMap       = array(
            'audienceMap'     => P\alias('some_alias_1'),
            'notificationMap' => array('message' => 'Some message 1'),
            'deviceTypeList'  => array('ios'),
        );

        $this->createNewBatch($notificationName, $payloadMap);

        // Second notification
        $notificationName = 'some_notification_identifier_2';
        $payloadMap       = array(
            'audienceMap'     => P\alias('some_alias_2'),
            'notificationMap' => array('message' => 'Some message 2'),
            'deviceTypeList'  => array('android'),
            'message'         => array(
                'title' => 'some title',
                'body'  => 'some body',
            ),
            'options'         => array('expiry' => '2015-04-01T12:00:00'),
        );

        $this->createNewBatch($notificationName, $payloadMap);

        $this
            ->airship
            ->expects($this->once())
            ->method('buildUrl')
            ->with('/api/push/')
            ->will($this->returnValue($uri));

        $this
            ->airship
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                json_encode($this->batchPushRequest->getPayload()),
                $uri,
                'application/json',
                3
            )
            ->will($this->returnValue($requestResponse));

        $response = $this->batchPushRequest->send();

        $this->assertEquals(new PushResponse($requestResponse), $response);
        $this->assertEquals($this->getTestWithMessageAndOptionsExpectedPayload(), $this->batchPushRequest->getPayload());
    }

    /**
     * Create a new batch notification with payload.
     *
     * @param string $notificationName
     * @param array  $payloadMap
     */
    private function createNewBatch($notificationName, array $payloadMap)
    {
        $this->batchPushRequest->createNewNotification($notificationName);
        $this->batchPushRequest->setAudience($notificationName, $payloadMap['audienceMap']);
        $this->batchPushRequest->setNotification($notificationName, $payloadMap['notificationMap']);
        $this->batchPushRequest->setDeviceTypes($notificationName, $payloadMap['deviceTypeList']);

        if (isset($payloadMap['message']))
        {
            $this->batchPushRequest->setMessage($notificationName, $payloadMap['message']);
        }

        if (isset($payloadMap['options']))
        {
            $this->batchPushRequest->setOptions($notificationName, $payloadMap['options']);
        }
    }

    /**
     * Retrieve the expected Payload.
     *
     * @return array
     */
    private function getTestWithABatchOfTwoExpectedPayload()
    {
        return array(
            array(
                'audience' => array(
                    'alias' => 'some_alias_1',
                ),
                'notification' => array(
                    'message' => 'Some message 1',
                ),
                'device_types' => array(
                    'ios',
                ),
            ),
            array(
                'audience' => array(
                    'alias' => 'some_alias_2',
                ),
                'notification' => array(
                    'message' => 'Some message 2',
                ),
                'device_types' => array(
                    'android',
                ),
            ),
        );
    }

    /**
     * Retrieve the expected Payload.
     *
     * @return array
     */
    private function getTestWithMessageAndOptionsExpectedPayload()
    {
        return array(
            array(
                'audience' => array(
                    'alias' => 'some_alias_1',
                ),
                'notification' => array(
                    'message' => 'Some message 1',
                ),
                'device_types' => array(
                    'ios',
                ),
            ),
            array(
                'audience' => array(
                    'alias' => 'some_alias_2',
                ),
                'notification' => array(
                    'message' => 'Some message 2',
                ),
                'device_types' => array(
                    'android',
                ),
                'message' => array(
                    'title' => 'some title',
                    'body'  => 'some body',
                ),
                'options' => array(
                    'expiry' => '2015-04-01T12:00:00',
                ),
            ),
        );
    }

    /**
     * Retrieve the request response.
     *
     * @param array  $rawBody
     *
     * @return array
     */
    private function getResponse(array $rawBody)
    {
        $rawHeaders = <<<HEREDOC
HTTP/1.1 202 Accepted
Content-Type: application/vnd.urbanairship+json; version=3
Data-Attribute: push_ids
Cache-Control: max-age=0
Expires: Fri, 01 May 2015 19:57:23 GMT
Last-Modified: Fri, 01 May 2015 19:57:23 GMT
Server: Jetty(8.y.z-SNAPSHOT)
Date: Fri, 01 May 2015 19:57:24 GMT
Connection: close
HEREDOC;

        $response              = new StdClass();
        $response->code        = 202;
        $response->raw_headers = $rawHeaders;
        $response->raw_body    = json_encode($rawBody);

        return $response;
    }
}
