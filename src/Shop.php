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

namespace Dominservice\PayuMarketplace;

use Dominservice\PayuMarketplace\Api\Configuration;
use Dominservice\PayuMarketplace\Api\Http;
use Dominservice\PayuMarketplace\Api\PayU;
use Dominservice\PayuMarketplace\Api\Model\PayuShop;
use Dominservice\PayuMarketplace\Api\Model\PayuShopBalance;
use Dominservice\PayuMarketplace\Api\ResultError;
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\AuthType\Oauth as AuthType_Oauth;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;

class Shop extends PayU
{
    const SHOPS_SERVICE = 'shops';

    /**
     * Retrieving shop data
     * @param $publicShopId
     * @return PayuShop
     * @throws ConfigException
     * @throws Exception\NetworkException
     * @throws PayuMarketplaceException
     */
    public static function get($publicShopId)
    {
        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        if (!$authType instanceof AuthType_Oauth) {
            throw new ConfigException('Get shop works only with OAuth');
        }

        $pathUrl = Configuration::getServiceUrl() . self::SHOPS_SERVICE . '/' . $publicShopId;

        return self::verifyResponse(Http::doGet($pathUrl, $authType));
    }

    /**
     * @param $response
     * @return PayuShop|void
     * @throws Exception\NetworkException
     * @throws Exception\ServerMaintenanceException
     * @throws PayuMarketplaceException
     * @throws ServerErrorException
     */
    public static function verifyResponse($response)
    {
        $httpStatus = $response['code'];

        if ($httpStatus == 500) {
            $result = (new ResultError())
                ->setErrorDescription($response['response']);
            Http::throwErrorHttpStatusException($httpStatus, $result);
        }

        $message = json_decode($response['response'], true);

        if (json_last_error() === JSON_ERROR_SYNTAX) {
            throw new ServerErrorException('Incorrect json response. Response: [' . $response['response'] . ']');
        }

        if ($httpStatus == 200) {
            return (new PayuShop())
                ->setShopId($message['shopId'])
                ->setName($message['name'])
                ->setCurrencyCode($message['currencyCode'])
                ->setBalance(
                    (new PayuShopBalance())
                        ->setCurrencyCode($message['balance']['currencyCode'])
                        ->setTotal($message['balance']['total'])
                        ->setAvailable($message['balance']['available'])
                );
        }

        $result = (new ResultError())
            ->setError($message['error'])
            ->setErrorDescription($message['error_description']);

        Http::throwErrorHttpStatusException($httpStatus, $result);
    }
}
