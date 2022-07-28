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

namespace Dominservice\PayuMarketplace\Api;

use Dominservice\PayuMarketplace\AuthType\TokenRequest as AuthType_TokenRequest;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;

class Oauth
{
    /**
     * @var OauthCacheInterface
     */
    private static $oauthTokenCache;

    const CACHE_KEY = 'AccessToken';

    /**
     * @param $clientId
     * @param $clientSecret
     * @return OauthResultClientCredentials|void
     * @throws ServerErrorException
     * @throws \Dominservice\PayuMarketplace\Exception\ConfigException
     * @throws \Dominservice\PayuMarketplace\Exception\NetworkException
     */
    public static function getAccessToken($clientId = null, $clientSecret = null)
    {
        if (Configuration::getOauthGrantType() === OauthGrantType::TRUSTED_MERCHANT) {
            return self::retrieveAccessToken($clientId, $clientSecret);
        }

        $cacheKey = self::CACHE_KEY . Configuration::getOauthClientId();

        self::getOauthTokenCache();

        $tokenCache = self::$oauthTokenCache->get($cacheKey);

        if ($tokenCache instanceof OauthResultClientCredentials && !$tokenCache->hasExpire()) {
            return $tokenCache;
        }

        self::$oauthTokenCache->invalidate($cacheKey);
        $response =  self::retrieveAccessToken($clientId, $clientSecret);
        self::$oauthTokenCache->set($cacheKey, $response);

        return $response;
    }

    /**
     * @param $clientId
     * @param $clientSecret
     * @return OauthResultClientCredentials|void
     * @throws ServerErrorException
     * @throws \Dominservice\PayuMarketplace\Exception\ConfigException
     * @throws \Dominservice\PayuMarketplace\Exception\NetworkException
     */
    private static function retrieveAccessToken($clientId, $clientSecret)
    {
        $authType = new AuthType_TokenRequest();

        $oauthUrl = Configuration::getOauthEndpoint();
        $data = array(
            'grant_type' => Configuration::getOauthGrantType(),
            'client_id' => $clientId ? $clientId : Configuration::getOauthClientId(),
            'client_secret' => $clientSecret ? $clientSecret : Configuration::getOauthClientSecret()
        );

        if (Configuration::getOauthGrantType() === OauthGrantType::TRUSTED_MERCHANT) {
            $data['email'] = Configuration::getOauthEmail();
            $data['ext_customer_id'] = Configuration::getOauthExtCustomerId();
        }

        return self::parseResponse(Http::doPost($oauthUrl, http_build_query($data, '', '&'), $authType));
    }

    /**
     * Parse response from PayU
     *
     * @param $response
     * @return OauthResultClientCredentials|void
     * @throws Exception\NetworkException
     * @throws Exception\PayuMarketplaceException
     * @throws Exception\ServerMaintenanceException
     * @throws ServerErrorException
     */
    private static function parseResponse($response)
    {
        $httpStatus = $response['code'];

        if ($httpStatus == 500) {
            $result = new ResultError();
            $result->setErrorDescription($response['response']);
            Http::throwErrorHttpStatusException($httpStatus, $result);
        }

        $message = Util::convertJsonToArray($response['response'], true);

        if (json_last_error() == JSON_ERROR_SYNTAX) {
            throw new ServerErrorException('Incorrect json response. Response: [' . $response['response'] . ']');
        }

        if ($httpStatus == 200) {
            $result = new OauthResultClientCredentials();
            $result->setAccessToken($message['access_token'])
                ->setTokenType($message['token_type'])
                ->setExpiresIn($message['expires_in'])
                ->setGrantType($message['grant_type'])
                ->calculateExpireDate(new \DateTime());

            return $result;
        }

        $result = new ResultError();
        $result->setError($message['error'])
            ->setErrorDescription($message['error_description']);

        Http::throwErrorHttpStatusException($httpStatus, $result);
    }

    /**
     * @return void
     */
    private static function getOauthTokenCache()
    {
        $oauthTokenCache = Configuration::getOauthTokenCache();

        if (!$oauthTokenCache instanceof OauthCacheInterface) {
            $oauthTokenCache = new OauthCacheFile();
            Configuration::setOauthTokenCache($oauthTokenCache);
        }

        self::$oauthTokenCache = $oauthTokenCache;
    }
}
