<?php

namespace UrbanAirship\Push;

use UrbanAirship\Airship;
use UrbanAirship\UALog;

abstract class BasePushRequest
{
    /**
     * @var \UrbanAirship\Airship
     */
    protected $airship;

    /**
     * Constructor.
     *
     * @param \UrbanAirship\Airship $airship
     */
    public function __construct(Airship $airship)
    {
        $this->airship = $airship;
    }

    /**
     * Retrieve the url api endpoint to send the push request to.
     *
     * @return string
     */
    abstract protected function getUrl();

    /**
     * Retrieve the push payload.
     *
     * @return array
     */
    abstract public function getPayload();

    /**
     * Retrieve the sub-class success message for the log.
     *
     * @return string
     */
    abstract protected function getLogMessage();

    /**
     * Retrieve the key to use on the response payload for the log message.
     *
     * @return string
     */
    abstract protected function getMessagePayloadKey();

    /**
     * Send the push request.
     *
     * @return \UrbanAirship\Push\PushResponse
     */
    public function send()
    {
        $url        = $this->getUrl();
        $uri        = $this->airship->buildUrl($url);
        $logger     = UALog::getLogger();
        $response   = $this->airship->request('POST', json_encode($this->getPayload()), $uri, 'application/json', 3);
        $payload    = json_decode($response->raw_body, true);
        $payloadKey = $this->getMessagePayloadKey();

        $logger->info($this->getLogMessage(), array($payloadKey => $payload[$payloadKey]));

        return new PushResponse($response);
    }
}
