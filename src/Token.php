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
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;

class Token extends PayU
{

    const TOKENS_SERVICE = 'tokens';

    /**
     * Deleting a payment token
     *
     * @param $token
     * @return OpenPayU_Result|null
     * @throws ConfigException
     * @throws Exception\NetworkException
     * @throws PayuMarketplaceException
     */
    public static function delete($token)
    {

        try {
            $authType = self::getAuth();
        } catch (OpenPayU_Exception $e) {
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
     * @return null|Api\Result
     * @throws Exception\AuthException
     * @throws Exception\NetworkException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
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
            Http::throwHttpStatusException($httpStatus, $result);
        }
    }
}
