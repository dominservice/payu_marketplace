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

namespace Dominservice\PayuMarketplace\AuthType;

use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;

class Oauth implements AuthType
{
    /**
     * @var OauthResultClientCredentials
     */
    private $oauthResult;

    public function __construct($clientId, $clientSecret)
    {
        if (empty($clientId)) {
            throw new ConfigException('ClientId is empty');
        }

        if (empty($clientSecret)) {
            throw new ConfigException('ClientSecret is empty');
        }

        try {
            $this->oauthResult = \Dominservice\PayuMarketplace\Api\Oauth::getAccessToken();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException('Oauth error: [code=' . $e->getCode() . '], [message=' . $e->getMessage() . ']');
        }
    }

    public function getHeaders()
    {
        return array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->oauthResult->getAccessToken()
        );
    }
}
