<?php
/**
 * PayU Marketplace Library based on OpenPayU Standard Library (openpayu/openpayu)
 *
 * @package   PayuMarketplace
 * @author    DSO-IT Mateusz Domin <biuro@dso.biz.pl>
 * @copyright 2022 DSO-IT Mateusz Domin
 * @license   http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 * @version   1.0.0
 */

namespace Dominservice\PayuMarketplace\Api\Oauth\AuthType;

use Dominservice\PayuMarketplace\Api\Oauth\OauthResultClientCredentials;
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;

class Oauth implements AuthType
{
    /**
     * @var OauthResultClientCredentials
     */
    private $oauthResult;
    private $headers;

    public function __construct($clientId, $clientSecret)
    {
        if (empty($clientId)) {
            throw new ConfigException('ClientId is empty');
        }

        if (empty($clientSecret)) {
            throw new ConfigException('ClientSecret is empty');
        }

        try {
            $this->oauthResult = \Dominservice\PayuMarketplace\Api\Oauth\Oauth::getAccessToken();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException('Oauth error: [code=' . $e->getCode() . '], [message=' . $e->getMessage() . ']');
        }

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->oauthResult->getAccessToken(),
        ];
    }

    public function setHeader($key, $val)
    {
        $this->headers[$key] = $val;
        return $this;
    }

    public function getHeaders()
    {
        $headers = [];

        foreach ($this->headers as $key=>$val) {
            $headers[] = $key . ': ' . $val;
        }

        return $headers;
    }
}
