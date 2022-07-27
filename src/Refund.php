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
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;

class Refund extends PayU
{
    /**
     * Function make refund for order
     * @param $orderId
     * @param $description
     * @param null|int $amount Amount of refund in pennies
     * @param null|string $extCustomerId Marketplace external customer ID
     * @param null|string $extRefundId Marketplace external refund ID
     * @return null|OpenPayU_Result
     * @throws OpenPayU_Exception
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
     * @param string $response
     * @param string $messageName
     * @return OpenPayU_Result
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
            Http::throwHttpStatusException($httpStatus, $result);
        }
    }
}