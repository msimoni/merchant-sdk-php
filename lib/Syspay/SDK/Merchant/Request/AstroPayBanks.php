<?php

/**
 * Get the available banks to process AstroPay payments with.
 */
class Syspay_Merchant_AstroPayBanksRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/astropay/banks/%s';

    /**
     * @var string
     */
    private $country;

    public function __construct($country = null)
    {
        if (null !== $country) {
            $this->setCountry($country);
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
        return sprintf(self::PATH, $this->country);
    }


    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->banks) || !is_array($response->banks)) {
            throw new Syspay_Merchant_UnexpectedResponseException(
                'Unable to retrieve "banks" data from response',
                $response
            );
        }

        $banks = array();
        foreach ($response->banks as $bank) {
            array_push(
                $banks,
                Syspay_Merchant_Entity_AstroPayBank::buildFromResponse($bank)
            );
        }

        return $banks;
    }

    /**
     * Gets the value of country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Sets the value of country.
     *
     * @param string $country the country
     *
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = strtoupper($country);

        return $this;
    }
}
