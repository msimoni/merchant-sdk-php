<?php

/**
 * Process a refund
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#make-a-refund
 */
class Syspay_Merchant_RefundRequest extends Syspay_Merchant_Request
{
    const METHOD = 'POST';
    const PATH   = '/api/v1/merchant/refund';

    /**
     * @var integer
     */
    private $paymentId;

    /**
     * @var Syspay_Merchant_Entity_Refund
     */
    private $refund;

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
        return self::PATH;
    }

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->refund)) {
            throw new Syspay_Merchant_UnexpectedResponseException(
                'Unable to retrieve "refund" data from response',
                $response
            );
        }

        $refund = Syspay_Merchant_Entity_Refund::buildFromResponse($response->refund);

        return $refund;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = $this->refund->toArray();
        $data['payment_id'] = $this->paymentId;

        return $data;
    }


    /**
     * Gets the value of paymentId.
     *
     * @return integer
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Sets the value of paymentId.
     *
     * @param integer $paymentId the paymentId
     *
     * @return self
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * Gets the value of refund.
     *
     * @return Syspay_Merchant_Entity_Refund
     */
    public function getRefund()
    {
        return $this->refund;
    }

    /**
     * Sets the value of refund.
     *
     * @param Syspay_Merchant_Entity_Refund $refund the refund
     *
     * @return self
     */
    public function setRefund(Syspay_Merchant_Entity_Refund $refund)
    {
        $this->refund = $refund;

        return $this;
    }
}
