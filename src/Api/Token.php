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

use Dominservice\PayuMarketplace\Api;
use Dominservice\PayuMarketplace\Api\Oauth\AuthType\Oauth as AuthType_Oauth;
use Dominservice\PayuMarketplace\Api\Oauth\OauthGrantType;
use Dominservice\PayuMarketplace\Exception;
use Dominservice\PayuMarketplace\Exception\AuthException;
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\NetworkException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\Exception\RequestException;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;
use Dominservice\PayuMarketplace\Exception\ServerMaintenanceException;

class Token extends PayU
{

    const TOKENS_SERVICE = 'tokens';

    /**
     * @param $token
     * @return Result|null
     * @throws AuthException
     * @throws ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function delete($token)
    {

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        if (!$authType instanceof AuthType_Oauth) {
            throw new ConfigException('Delete token works only with OAuth');
        }

        if (Configuration::getOauthGrantType() !== OauthGrantType::TRUSTED_MERCHANT) {
            throw new ConfigException('Token delete request is available only for trusted_merchant');
        }

        $pathUrl = Configuration::getServiceUrl() . self::TOKENS_SERVICE . '/' . $token;

        $response = self::verifyResponse(Http::doDelete($pathUrl, $authType));

        return $response;
    }

    /**
     * @param $response
     * @return Result|null
     * @throws AuthException
     * @throws NetworkException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
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

        if ($httpStatus == 204) {
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

        return null;
    }
}
