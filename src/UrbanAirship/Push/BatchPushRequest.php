<?php
/**
 * @copyright 2015 UrbanAirship and contributors.
 */

namespace UrbanAirship\Push;

use UrbanAirship\Push\Exception\BatchPushRequestException;

class BatchPushRequest extends BasePushRequest
{
    /**
     * @var array
     */
    private $batchPayloadMap;

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
    public function getPayload()
    {
        return array_values($this->batchPayloadMap);
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogMessage()
    {
        return 'Batch push sent successfully.';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMessagePayloadKey()
    {
        return 'push_ids';
    }

    /**
     * Create a new notification by given name.
     *
     * @param string $notificationName
     *
     * @throws \UrbanAirship\Push\Exception\BatchPushRequestException
     */
    public function createNewNotification($notificationName)
    {
        if ( ! is_string($notificationName)) {
            throw new BatchPushRequestException('Notification name must be a string.');
        }

        if (isset($this->batchPayloadMap[$notificationName])) {
            return;
        }

        $this->batchPayloadMap[$notificationName] = array();
    }

    /**
     * Set the audience for a given notification.
     *
     * @param string $notificationName
     * @param mixed  $audience
     *
     * @return $this
     */
    public function setAudience($notificationName, $audience)
    {
        $this->validateNotificationName($notificationName);

        $this->batchPayloadMap[$notificationName]['audience'] = $audience;

        return $this;
    }

    /**
     * Set the notification for the given notification name.
     *
     * @param string $notificationName
     * @param array  $notificationMap
     *
     * @return $this
     */
    public function setNotification($notificationName, array $notificationMap)
    {
        $this->validateNotificationName($notificationName);

        $this->batchPayloadMap[$notificationName]['notification'] = $notificationMap;

        return $this;
    }

    /**
     * Set the device types for the given notification name.
     *
     * @param string $notificationName
     * @param mixed  $deviceTypes
     *
     * @return $this
     */
    public function setDeviceTypes($notificationName, $deviceTypes)
    {
        $this->validateNotificationName($notificationName);

        $this->batchPayloadMap[$notificationName]['device_types'] = $deviceTypes;

        return $this;
    }

    /**
     * Push a device type into the device types list for the given notification name.
     *
     * @param string $notificationName
     * @param string $deviceType
     *
     * @return $this
     */
    public function pushDeviceType($notificationName, $deviceType)
    {
        $this->validateNotificationName($notificationName);

        if (isset($this->batchPayloadMap[$notificationName]['deviceType'][$deviceType])) {
            return $this;
        }

        $this->batchPayloadMap[$notificationName]['deviceTypes'][] = $deviceType;

        return $this;
    }

    /**
     * Set the message data for a rich message push.
     *
     * @param string $notificationName
     * @param array  $messageMap
     *
     * @return $this
     */
    public function setMessage($notificationName, array $messageMap)
    {
        $this->validateNotificationName($notificationName);

        $this->batchPayloadMap[$notificationName]['message'] = $messageMap;

        return $this;
    }

    /**
     * Set the options for given notification name.
     *
     * @param string $notificationName
     * @param array $options
     *
     * @return $this
     * @throws \UrbanAirship\Push\Exception\BatchPushRequestException
     */
    public function setOptions($notificationName, array $options)
    {
        $this->validateNotificationName($notificationName);

        $this->batchPayloadMap[$notificationName]['options'] = $options;

        return $this;
    }

    /**
     * Ensure that the notification name space is defined.
     *
     * @param string $notificationName
     *
     * @throws \UrbanAirship\Push\Exception\BatchPushRequestException
     */
    private function validateNotificationName($notificationName)
    {
        if ( ! isset($this->batchPayloadMap[$notificationName])) {
            $message = sprintf('Notification name "%s" is not defined.', $notificationName);

            throw new BatchPushRequestException($message);
        }
    }

    /**
     * Retrieve the number of elements within the batch payload map.
     *
     * @return integer
     */
    public function getPayloadCount()
    {
        return count($this->batchPayloadMap);
    }

    /**
     * Reset the batch payload map.
     */
    public function resetBatchPayloadMap()
    {
        $this->batchPayloadMap = array();
    }
}
