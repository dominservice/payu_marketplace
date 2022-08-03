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
use Dominservice\PayuMarketplace\Exception\NetworkException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\Exception\RequestException;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;
use Dominservice\PayuMarketplace\Exception\ServerMaintenanceException;

class Refund extends PayU
{
    /**
     * @param $orderId
     * @param $description
     * @param $amount
     * @param $extCustomerId
     * @param $extRefundId
     * @return \Dominservice\PayuMarketplace\Api\Result|null
     * @throws AuthException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     * @throws \Dominservice\PayuMarketplace\Exception\ConfigException
     */
    public static function create($orderId, $description, $amount = null, $extCustomerId = null, $extRefundId = null)
    {
        if (empty($orderId)) {
            throw new PayuMarketplaceException('Invalid orderId value for refund');
        }

        if (empty($description)) {
            throw new PayuMarketplaceException('Invalid description of refund');
        }
        $refund = array(
            'orderId' => $orderId,
            'refund' => array('description' => $description)
        );

        if (!empty($amount)) {
            $refund['refund']['amount'] = $amount;
        }

        if (!empty($extCustomerId)) {
            $refund['refund']['extCustomerId'] = $extCustomerId;
        }

        if (!empty($extRefundId)) {
            $refund['refund']['extRefundId'] = $extRefundId;
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getServiceUrl().'orders/'. $refund['orderId'] . '/refund';

        $data = Util::buildJsonFromArray($refund);

        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'RefundCreateResponse');

        return $result;
    }

    /**
     * @param $response
     * @param $messageName
     * @return \Dominservice\PayuMarketplace\Api\Result|void
     * @throws AuthException
     * @throws NetworkException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function verifyResponse($response, $messageName='')
    {
        $data = array();
        $httpStatus = $response['code'];

        $message = Util::convertJsonToArray($response['response'], true);

        $data['status'] = isset($message['status']['statusCode']) ? $message['status']['statusCode'] : null;

        if (json_last_error() == JSON_ERROR_SYNTAX) {
            $data['response'] = $response['response'];
        } elseif (isset($message[$messageName])) {
            unset($message[$messageName]['Status']);
            $data['response'] = $message[$messageName];
        } elseif (isset($message)) {
            $data['response'] = $message;
            unset($message['status']);
        }

        $result = self::build($data);

        if ($httpStatus == 200 || $httpStatus == 201 || $httpStatus == 422 || $httpStatus == 302) {
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