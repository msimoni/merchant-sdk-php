<?php

/**
 * An AstroPay Bank
 */
class Syspay_Merchant_Entity_AstroPayBank extends Syspay_Merchant_Entity
{
    /**
     * The bank code
     * @var string
     */
    private $code;

    /**
     * The bank name
     * @var string
     */
    private $name;

    /**
     * The full URL to the bank logo
     * @var string
     */
    private $logoUrl;

    /**
     * {@inheritDoc}
     */
    public static function buildFromResponse(stdClass $response)
    {
        $bank = new self();
        $bank->setCode(isset($response->code)?$response->code:null);
        $bank->setName(isset($response->name)?$response->name:null);
        $bank->setLogoUrl(isset($response->logo)?$response->logo:null);
        return $bank;
    }

    /**
     * Gets the The bank code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets the The bank code.
     *
     * @param string $code the code
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Gets the The bank name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the The bank name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the The full URL to the bank logo.
     *
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * Sets the The full URL to the bank logo.
     *
     * @param string $logoUrl the logo url
     *
     * @return self
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }
}
