<?php

/**
 * Base API client
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#emerchant-rest-api
 */
class Syspay_Merchant_Client
{
    const BASE_URL_PROD    = 'https://app.syspay.com';
    const BASE_URL_SANDBOX = 'https://app-sandbox.syspay.com';

    protected $username;
    protected $secret;
    protected $baseUrl;

    protected $responseBody    = null;
    protected $responseHeaders = array();
    protected $responseData    = null;

    protected $requestBody     = null;
    protected $requestHeaders  = array();
    protected $requestParams   = null;

    protected $requestId       = null;

    /**
     * Creates a new client object
     * @param string $username The Syspay API username
     * @param string $secret   The Syspay API shared secret
     * @param string $baseUrl  The base URL the request should be made to (optional, defaults to prod environment)
     */
    public function __construct($username, $secret, $baseUrl = null)
    {
        $this->username = $username;
        $this->secret   = $secret;
        $this->baseUrl  = (null === $baseUrl)?self::BASE_URL_PROD:$baseUrl;
    }

    /**
     * Generates the x-wsse header
     *
     * @param  string   $username The Syspay API username
     * @param  string   $secret   The Syspay API shared secret
     * @param  string   $nonce    A random string (optional, will be generated)
     * @param  DateTime $created  The creation date of this header (optional, defaults to now)
     * @return string   The value to give to the x-wsse header
     */
    protected function generateAuthHeader($username, $secret, $nonce = null, DateTime $created = null)
    {
        if (null === $nonce) {
            $nonce = md5(rand(), true);
        }

        if (null === $created) {
            $created = new DateTime();
        }

        $created = $created->format('U');

        $digest = base64_encode(sha1($nonce . $created . $secret, true));
        $b64nonce = base64_encode($nonce);

        return sprintf(
            'AuthToken MerchantAPILogin="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $username,
            $digest,
            $b64nonce,
            $created
        );
    }

    /**
     * Make a request to the Syspay API
     * @param  Syspay_Merchant_Request $request The request to send to the API
     * @return mixed The response to the request
     * @throws Syspay_Merchant_RequestException If the request could not be processed by the API
     */
    public function request(Syspay_Merchant_Request $request)
    {
        $this->requestBody = $this->responseBody = $this->responseData = $this->requestId = null;
        $this->responseHeaders = $this->requestHeaders = array();

        $headers = array(
            'Accept: application/json',
            'X-Wsse: ' . $this->generateAuthHeader($this->username, $this->secret)
        );


        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($request->getPath(), '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // TODO: verify ssl and provide certificate in package
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $method = strtoupper($request->getMethod());

        // Per-method special handling
        switch($method) {
            case 'PUT':
            case 'POST':
                $body = json_encode($request->getData());

                array_push($headers, 'Content-Type: application/json');
                array_push($headers, 'Content-Length: ' . strlen($body));

                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                $this->requestBody = $body;
                break;

            case 'GET':
                $queryParams = $request->getData();
                if (is_array($queryParams)) {
                    $url .= '?' . http_build_query($queryParams);
                }
                $this->requestParams = $queryParams;
                break;

            case 'DELETE':
                break;

            default:
                throw new Exception('Unsupported method given: ' . $method);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $this->requestHeaders = $headers;

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        $this->responseHeaders = explode("\r\n", $headers);
        $this->responseBody    = $body;
        if (preg_match('/\nx-syspay-request-uuid: (.*?)\r?\n/i', $headers, $m)) {
            $this->requestId = $m[1];
        }

        if (!in_array($httpCode, array(200, 201))) {
            throw new Syspay_Merchant_RequestException($httpCode, $headers, $body);
        }

        $decoded = json_decode($body);

        if (($decoded instanceof stdClass) && isset($decoded->data) && ($decoded->data instanceof stdClass)) {
            $this->responseData = $decoded->data;
            return $request->buildResponse($decoded->data);
        } else {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to decode response from json', $body);
        }

        return false;
    }

    /**
     * Get the raw body of the last request.
     * @return string The last request's response body, or null if the request failed.
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * Get the raw headers of the last request.
     * @return array The last request's headers, or an empty array if the request failed
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * Get the username
     * @return string Merchant username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the shared secret
     * @return string secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Get the base URL
     * @return string Base URL
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get the (decoded) response data, if available
     * @return mixed Response data
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * Get the request headers, if available
     * @return array Request headers
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * Get the request body, if available (POST/PUT requests)
     * @return array Request body
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * Get the request params, if available (GET request)
     * @return array Request body
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    /**
     * Get the last request's id
     * @return string Request Id or null if it couldn't be extracted
     */
    public function getRequestId()
    {
        return $this->requestId;
    }
}
