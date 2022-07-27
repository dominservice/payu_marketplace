<?php

namespace Dominservice\PayuMarketplace;

class Api
{
    const TYPE_PAYOUT_ACCOUNT_DATA = 'PAYOUT_ACCOUNT_DATA';
    const TYPE_FULL = 'FULL';
    const TYPE_UPDATE = 'UPDATE';
    const TYPE_REVERIFICATION = 'REVERIFICATION';
    const TYPE_PERSONAL_ID_TAX_ID_CHANGE = 'PERSONAL_ID_TAX_ID_CHANGE';

    const STATUS_WAITING_FOR_DATA = 'WAITING_FOR_DATA';
    const STATUS_WAITING_FOR_VERIFICATION = 'WAITING_FOR_VERIFICATION';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_POSITIVE = 'POSITIVE';
    const STATUS_NEGATIVE = 'NEGATIVE';

    const LEGAL_FORM_PRIVATE_PERSON = 'PRIVATE_PERSON';
    const LEGAL_FORM_SOLE_TRADER = 'SOLE_TRADER';
    const LEGAL_FORM_LEGAL_ENTITY = 'LEGAL_ENTITY'; // - only for foreign, non EOG companies
    const LEGAL_FORM_ASSOCIATION = 'ASSOCIATION';
    const LEGAL_FORM_CIVIL_LAW_PARTNERSHIP = 'CIVIL_LAW_PARTNERSHIP';
    const LEGAL_FORM_FOREIGN_COMPANY = 'FOREIGN_COMPANY';
    const LEGAL_FORM_FOUNDATION = 'FOUNDATION';
    const LEGAL_FORM_GENERAL_PARTNERSHIP = 'GENERAL_PARTNERSHIP';
    const LEGAL_FORM_JOINT_STOCK_COMPANY = 'JOINT_STOCK_COMPANY';
    const LEGAL_FORM_LIMITED_JOINT_STOCK_PARTNERSHIP = 'LIMITED_JOINT_STOCK_PARTNERSHIP';
    const LEGAL_FORM_LIMITED_LIABILITY_COMPANY = 'LIMITED_LIABILITY_COMPANY';
    const LEGAL_FORM_LIMITED_LIABILITY_PARTNERSHIP = 'LIMITED_LIABILITY_PARTNERSHIP';
    const LEGAL_FORM_PROFESSIONAL_PARTNERSHIP = 'PROFESSIONAL_PARTNERSHIP';
    const LEGAL_FORM_LIMITED_PARTNERSHIP = 'LIMITED_PARTNERSHIP';
    const LEGAL_FORM_OTHER = 'OTHER';


    private $baseUrl = 'https://secure.payu.com/';

    private $verifyPath = 'verification';
    private $verifyAdvicePath = 'verification-advice';

    private $authPath = 'pl/standard/user/oauth/authorize';

    private $client_id;

    private $client_secret;

    private $access_token;

    private $token_type;

    private $expire_time_at;

    private $grant_type;

    public function __construct($client_id, $client_secret, $access_token)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token = $access_token;
    }

    protected function getClientId()
    {
        return $this->client_id;
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

    /**
     * @return bool
     */
    private function checkIsExpired()
    {
        if (time() >= $this->getExpireTimeAt(true)) {
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

    protected function getExpireTimeAt($checkExpired = false)
    {
        if (!$checkExpired) {
            $this->checkIsExpired();
        }
        return (int)$this->expire_time_at;
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
    protected function verification($sellerId, $type = Api::TYPE_FULL)
    {
        try {
            $requestData = 'sellerId='.$sellerId.'&type='.$type;
            list($err, $errno, $info, $data) = $this->curlRequest($this->verifyPath, 'POST', $requestData, [], 'json');

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
     * @param $postData
     * @return array
     */
    protected function curlRequest(
        $url,
        $method = 'GET',
        $requestData = null,
        $headers = [],
        $requestDataType = 'http_query',
        $options = [],
        $postData = []
    )
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
                if (!empty($postData)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                }
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