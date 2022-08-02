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

use Dominservice\PayuMarketplace\Exception\AuthException;
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\NetworkException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\Exception\RequestException;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;
use Dominservice\PayuMarketplace\Exception\ServerMaintenanceException;

class Http
{
    /**
     * @param $pathUrl
     * @param $data
     * @param $authType
     * @return array
     * @throws ConfigException
     * @throws NetworkException
     */
    public static function doPost($pathUrl, $data, $authType)
    {
        $response = HttpCurl::doPayuRequest('POST', $pathUrl, $authType, $data);

        return $response;
    }

    /**
     * @param $pathUrl
     * @param $authType
     * @return array
     * @throws ConfigException
     * @throws NetworkException
     */
    public static function doGet($pathUrl, $authType)
    {
        $response = HttpCurl::doPayuRequest('GET', $pathUrl, $authType);

        return $response;
    }

    /**
     * @param $pathUrl
     * @param $authType
     * @return array
     * @throws ConfigException
     * @throws NetworkException
     */
    public static function doDelete($pathUrl, $authType)
    {
        $response = HttpCurl::doPayuRequest('DELETE', $pathUrl, $authType);

        return $response;
    }

    /**
     * @param $pathUrl
     * @param $data
     * @param $authType
     * @return array
     * @throws ConfigException
     * @throws NetworkException
     */
    public static function doPut($pathUrl, $data, $authType)
    {
        $response = HttpCurl::doPayuRequest('PUT', $pathUrl, $authType, $data);

        return $response;
    }

    /**
     * @param $statusCode
     * @param $message
     * @return mixed
     * @throws AuthException
     * @throws NetworkException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function throwHttpStatusException($statusCode, $message = null)
    {

        $response = $message->getResponse();
        $statusDesc = isset($response->status->statusDesc) ? $response->status->statusDesc : '';
//dump($response);
        switch ($statusCode) {
            case 400:
                throw new RequestException($message, $message->getStatus().' - '.$statusDesc, $statusCode);
                break;

            case 401:
            case 403:
                throw new AuthException($message->getStatus().' - '.$statusDesc, $statusCode);
                break;

            case 404:
                throw new NetworkException($message->getStatus().' - '.$statusDesc, $statusCode);
                break;

            case 408:
                throw new ServerErrorException('Request timeout', $statusCode);
                break;

            case 500:


                dump($response, $statusCode, $statusDesc, $message);

                throw new ServerErrorException('PayU system is unavailable or your order is not processed.
                Error:
                [' . $statusDesc . ']', $statusCode);
                break;

            case 503:
                throw new ServerMaintenanceException('Service unavailable', $statusCode);
                break;

            default:
                dump(['test', $response, $statusCode]);
                throw new NetworkException('Unexpected HTTP code response', $statusCode);
                break;

        }
    }

    /**
     * @param $statusCode
     * @param ResultError $resultError
     * @return mixed
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function throwErrorHttpStatusException($statusCode, $resultError)
    {
        switch ($statusCode) {
            case 400:
                throw new PayuMarketplaceException($resultError->getError()
                    .' - '.$resultError->getErrorDescription(), $statusCode);
                break;

            case 401:
            case 403:
                throw new ServerErrorException($resultError->getError()
                    .' - '.$resultError->getErrorDescription(), $statusCode);
                break;

            case 404:
                throw new NetworkException($resultError->getError()
                    .' - '.$resultError->getErrorDescription(), $statusCode);
                break;

            case 408:
                throw new ServerErrorException('Request timeout', $statusCode);
                break;

            case 500:
                throw new ServerErrorException('PayU system is unavailable. Error: ['
                    . $resultError->getErrorDescription() . ']', $statusCode);
                break;

            case 503:
                throw new ServerMaintenanceException('Service unavailable', $statusCode);
                break;

            default:
                throw new NetworkException('Unexpected HTTP code response', $statusCode);
                break;

        }
    }
}
