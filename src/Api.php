<?php

namespace Dominservice\PayuMarketplace;

class Api
{
    private $baseUrl = 'https://secure.payu.com/';

    private $verifyPath = 'verification-advice';
    private $verifyAdvicePath = 'verification-advice';

    private $authPath = 'pl/standard/user/oauth/authorize';

    private $client_id;

    private $client_secret;

    private $access_token;

    private $token_type;

    private $expire_time_at;

    private $grant_type;

    public function __construct($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    /**
     * Authorization
     *
     * @return $this
     */
    protected function auth()
    {
        $requestData = 'grant_type=client_credentials&client_id='.$this->client_id.'&client_secret='.$this->client_secret;
        list($err, $errno, $info, $data) = self::curlRequest($this->authPath, 'POST', $requestData);

        if (empty($err)) {
            $data = json_decode($data);

            if (!empty($data->access_token)) {
                $this->access_token = $data->access_token;
                $this->token_type = $data->token_type;
                $this->expire_time_at = time() + $data->expires_in;
                $this->grant_type = $data->grant_type;
            }
        }
        return $this;
    }

    private function checkIsExpired()
    {
        if (time() >= $this->getExpireTimeAt()) {
            $this->access_token = null;
            $this->token_type = null;
            $this->expire_time_at = null;
            $this->grant_type = null;

            return true;
        }

        return false;
    }

    protected function getAccessToken()
    {
        $this->checkIsExpired();
        return $this->access_token;
    }

    protected function getTokenType()
    {
        $this->checkIsExpired();
        return $this->token_type;
    }

    protected function getExpireTimeAt()
    {
        $this->checkIsExpired();
        return $this->expire_time_at;
    }

    protected function getGrantType()
    {
        $this->checkIsExpired();
        return $this->grant_type;
    }

    /**
     * Checking Registration
     *
     * @param $identificationNumber
     * @return mixed|null
     */
    protected function verificationAdvice($identificationNumber)
    {
        try {
            list($err, $errno, $info, $data) = $this->curlRequest($this->verifyAdvicePath . '/' . $identificationNumber);

            if (empty($err)) {
                return json_decode($data);
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Initializing Verification
     *
     * @param $sellerId
     * @param $type
     * @return mixed|null
     */
    protected function verification($sellerId, $type = Verification::TYPE_FULL)
    {
        try {
            $requestData = 'sellerId='.$sellerId.'&type='.$type;
            list($err, $errno, $info, $data) = $this->curlRequest($this->verifyPath, 'POST', $requestData);

            if (empty($err)) {
                return json_decode($data);
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * @param $url
     * @param $method
     * @param $requestData
     * @param $headers
     * @param $requestDataType
     * @param $options
     * @return array
     */
    protected function curlRequest($url, $method = 'GET', $requestData = null, $headers = [], $requestDataType = 'http_query', $options = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl.$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($requestData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($requestDataType === 'http_query') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
            } elseif ($requestDataType === 'json') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            } else {
                if (is_array($requestData)) {
                    $data = [];
                    foreach ($requestData as $key => $val) {
                        $data[] = [$key .'=' . (string)$val];
                    }
                    curl_setopt($ch, CURLOPT_POSTFIELDS, implode(' ', $data));
                } elseif (is_string($requestData) || is_numeric($requestData)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                }
            }
        }

        if ($this->getAccessToken()) {
            $headers[] = "Authorization: Bearer " . $this->getAccessToken();
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($options)) {
            if (isset($options['encoding'])) {
                curl_setopt($ch, CURLOPT_ENCODING, $options['encoding']);
            }
            if (isset($options['maxredis'])) {
                curl_setopt($ch, CURLOPT_MAXREDIRS, (int)$options['maxredis']);
            }
            if (isset($options['timeout'])) {
                curl_setopt($ch, CURLOPT_TIMEOUT, (int)$options['timeout']);
            }
            if (isset($options['http_version'])) {
                if ($options['http_version'] === "1_0") {
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                } elseif ($options['http_version'] === "1_1") {
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                } elseif ($options['http_version'] === "2_0") {
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
                }
            }
        }

        $data    = curl_exec($ch);
        $err     = curl_error($ch);
        $errno   = (int)curl_errno($ch);
        $info    = curl_getinfo($ch);
        curl_close($ch);

        return [$err, $errno, $info, $data];
    }

}