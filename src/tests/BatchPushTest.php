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
    public function testBatchNotificationRequestWithABatchOfTwo()
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
        $this->assertEquals($this->getExpectedPayload(), $this->batchPushRequest->getPayload());
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
    private function getExpectedPayload()
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
                'device_types' => array (
                    'android',
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
