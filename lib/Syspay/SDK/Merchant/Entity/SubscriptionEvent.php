<?php

/**
 * A subscription event object
 */
class Syspay_Merchant_Entity_SubscriptionEvent extends Syspay_Merchant_Entity
{
    const TYPE = 'subscription_event';

    const TYPE_TRIAL      = 'TRIAL';
    const TYPE_FREE_TRIAL = 'FREE_TRIAL';
    const TYPE_INITIAL    = 'INITIAL';
    const TYPE_BILL       = 'BILL';
    const TYPE_END        = 'END';

    /**
     * @var DateTime
     */
    protected $scheduledDate;

    /**
     * @var string
     */
    protected $eventType;

    /**
     * Build an event entity based on a json-decoded event stdClass
     *
     * @param  stdClass $response The subscription event
     * @return Syspay_Merchant_Entity_SubscriptionEvent The event object
     */
    public static function buildFromResponse(stdClass $response)
    {
        $event = new self();
        $event->setScheduledDate(
            isset($response->scheduled_date)?Syspay_Merchant_Utils::tsToDateTime($response->scheduled_date):null
        );
        $event->setEventType(isset($response->event_type)?$response->event_type:null);
        return $event;
    }

    /**
     * Gets the value of scheduledDate.
     *
     * @return DateTime
     */
    public function getScheduledDate()
    {
        return $this->scheduledDate;
    }

    /**
     * Sets the value of scheduledDate.
     *
     * @param DateTime $scheduledDate the scheduled date
     *
     * @return self
     */
    public function setScheduledDate(DateTime $scheduledDate = null)
    {
        $this->scheduledDate = $scheduledDate;

        return $this;
    }

    /**
     * Gets the value of eventType.
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * Sets the value of eventType.
     *
     * @param string $eventType the event type
     *
     * @return self
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }
}
