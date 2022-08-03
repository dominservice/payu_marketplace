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

use Dominservice\PayuMarketplace\Api\Model\PayuShop;
use Dominservice\PayuMarketplace\Api\Model\PayuShopBalance;
use Dominservice\PayuMarketplace\Api\Oauth\AuthType\Oauth as AuthType_Oauth;
use Dominservice\PayuMarketplace\Exception;
use Dominservice\PayuMarketplace\Exception\AuthException;
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\NetworkException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\Exception\RequestException;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;
use Dominservice\PayuMarketplace\Exception\ServerMaintenanceException;

class Shop extends PayU
{
    const SHOPS_SERVICE = 'shops';

    /**
     * @param $publicShopId
     * @return PayuShop|ResultError|null
     * @throws AuthException
     * @throws ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
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
     * @return PayuShop|ResultError|void
     * @throws AuthException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
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

        if(Configuration::getEnvironment() === 'sandbox') {
            Http::throwHttpStatusException($httpStatus, $result);
        } else {
            try {
                Http::throwHttpStatusException($httpStatus, $result);
            } catch (RequestException|AuthException|NetworkException|ServerErrorException|ServerMaintenanceException|\Throwable $exception) {
                $result->setError($exception->getMessage());
            }
            return $result;
        }
    }
}
