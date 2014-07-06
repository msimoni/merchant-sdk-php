<?php

/**
 * Issue a manual rebill on a subscription
 *
 * @see https://app.syspay.com/docs/api/merchant_subscription.html#issue-a-manual-rebill-on-a-subscription
 */
class Syspay_Merchant_SubscriptionRebillRequest extends Syspay_Merchant_Request
{
    const METHOD = 'POST';
    const PATH   = '/api/v1/merchant/subscription/%d/rebill';

    /**
     * @var integer
     */
    private $subscriptionId;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $extra;

    public function __construct($subscriptionId = null)
    {
        if (null !== $subscriptionId) {
            $this->setSubscriptionId($subscriptionId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return self::METHOD;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return sprintf(self::PATH, $this->subscriptionId);
    }

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->payment)) {
            throw new Syspay_Merchant_UnexpectedResponseException(
                'Unable to retrieve "payment" data from response',
                $response
            );
        }

        $payment = Syspay_Merchant_Entity_Payment::buildFromResponse($response->payment);

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = array();

        if (false === empty($this->reference)) {
            $data['reference'] = $this->reference;
        }

        if (false === empty($this->amount)) {
            $data['amount'] = $this->amount;
        }

        if (false === empty($this->currency)) {
            $data['currency'] = $this->currency;
        }

        if (false === empty($this->description)) {
            $data['description'] = $this->description;
        }

        if (false === empty($this->extra)) {
            $data['extra'] = $this->extra;
        }

        return $data;
    }

    /**
     * Gets the value of subscriptionId.
     *
     * @return integer
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * Sets the value of subscriptionId.
     *
     * @param integer $subscriptionId the subscriptionId
     *
     * @return self
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }

    /**
     * Gets the value of reference.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Sets the value of reference.
     *
     * @param string $reference the reference
     *
     * @return self
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Gets the value of amount.
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the value of amount.
     *
     * @param integer $amount the amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Gets the value of currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the value of currency.
     *
     * @param string $currency the currency
     *
     * @return self
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Gets the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param string $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of extra.
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Sets the value of extra.
     *
     * @param string $extra the extra
     *
     * @return self
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }
}
