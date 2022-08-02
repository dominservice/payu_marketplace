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
use Dominservice\PayuMarketplace\Api\Util;
use Dominservice\PayuMarketplace\AuthType\Oauth as AuthType_Oauth;
use Dominservice\PayuMarketplace\Exception\AuthException;
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\NetworkException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\Exception\RequestException;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;
use Dominservice\PayuMarketplace\Exception\ServerMaintenanceException;

class Retrieve extends PayU
{

    const PAYMETHODS_SERVICE = 'paymethods';

    /**
     * Get Pay Methods from POS
     * @param $lang
     * @return OpenPayU_Result|null
     * @throws ConfigException
     * @throws Exception\NetworkException
     * @throws PayuMarketplaceException
     */
    public static function payMethods($lang = null)
    {
        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        if (!$authType instanceof AuthType_Oauth) {
            throw new ConfigException('Retrieve works only with OAuth');
        }

        $pathUrl = Configuration::getServiceUrl() . self::PAYMETHODS_SERVICE;
        if ($lang !== null) {
            $pathUrl .= '?lang=' . $lang;
        }

        $response = self::verifyResponse(Http::doGet($pathUrl, $authType));

        return $response;
    }

    /**
     * @param string $response
     * @return null|Api\Result
     */
    public static function verifyResponse($response)
    {
        $data = array();
        $httpStatus = $response['code'];

        $message = Util::convertJsonToArray($response['response'], true);

        $data['status'] = isset($message['status']['statusCode']) ? $message['status']['statusCode'] : null;

        if (json_last_error() == JSON_ERROR_SYNTAX) {
            $data['response'] = $response['response'];
        } elseif (isset($message)) {
            $data['response'] = $message;
            unset($message['status']);
        }

        $result = self::build($data);

        if ($httpStatus == 200 || $httpStatus == 201 || $httpStatus == 422 || $httpStatus == 302 || $httpStatus == 400 || $httpStatus == 404) {
            return $result;
        } else {
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
}