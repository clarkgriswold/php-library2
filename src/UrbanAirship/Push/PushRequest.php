<?php
/**
 * @copyright 2013 Urban Airship and Contributors
 */

namespace UrbanAirship\Push;

use UrbanAirship\UALog;

class PushRequest extends BasePushRequest
{
    /**
     * @var mixed
     */
    private $audience;

    /**
     * @var array
     */
    private $notification;

    /**
     * @var mixed
     */
    private $deviceTypes;

    /**
     * @var array
     */
    private $message;

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    protected function getUrl()
    {
        return '/api/push/';
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogMessage()
    {
        return 'Push sent successfully.';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMessagePayloadKey()
    {
        return 'push_ids';
    }

    /**
     * {@inheritdoc}
     */
    function getPayload()
    {
        $payload = array(
            'audience'     => $this->audience,
            'notification' => $this->notification,
            'device_types' => $this->deviceTypes
        );

        if ( ! is_null($this->message)) {
            $payload['message'] = $this->message;
        }

        if ( ! is_null($this->options)) {
            $payload['options'] = $this->options;
        }

        return $payload;
    }

    /**
     * Set the audience for the notification.
     *
     * @param mixed $audience
     */
    function setAudience($audience)
    {
        $this->audience = $audience;

        return $this;
    }

    /**
     * Set the notifcation map for the notification.
     *
     * @param array $notification
     */
    function setNotification(array $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Set the device type(s) for the notification.
     *
     * @param mixed $deviceTypes
     */
    function setDeviceTypes($deviceTypes)
    {
        $this->deviceTypes = $deviceTypes;

        return $this;
    }

    /**
     * Set the message for the notification.
     *
     * @param array $message
     *
     * @see http://docs.urbanairship.com/api/ua.html#rich-push
     */
    function setMessage(array $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Define the options for the notification.
     *
     * @param array $options
     *
     * @see http://docs.urbanairship.com/api/ua.html#push-options
     */
    function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }
}
