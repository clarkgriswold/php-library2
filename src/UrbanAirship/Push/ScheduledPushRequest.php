<?php
/**
 * @copyright 2013 Urban Airship and Contributors
 */

namespace UrbanAirship\Push;

use UrbanAirship\UALog;

class ScheduledPushRequest extends BasePushRequest
{
    /**
     * @var array
     */
    private $schedule;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \UrbanAirship\Push\PushRequest
     */
    private $push;

    /**
     * {@inheritdoc}
     */
    protected function getUrl()
    {
        return '/api/schedules/';
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogMessage()
    {
        return 'Scheduled push sent successfully.';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMessagePayloadKey()
    {
        return 'schedule_urls';
    }

    /**
     * {@inheritdoc}
     */
    function getPayload()
    {
        $payload = array(
            'schedule' => $this->schedule,
            'push'     => $this->push->getPayload()
        );

        if ( ! is_null($this->name)) {
            $payload['name'] = $this->name;
        }

        return $payload;
    }

    /**
     * Set the schedule.
     *
     * @param array $schedule
     */
    function setSchedule(array $schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Set the schedule name.
     *
     * @param string $name
     */
    function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the push request.
     *
     * @param \UrbanAirship\Push\PushRequest $push
     */
    function setPush(PushRequest $push)
    {
        $this->push = $push;

        return $this;
    }
}
