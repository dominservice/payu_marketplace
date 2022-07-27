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
use Dominservice\PayuMarketplace\Api\HttpCurl;
use Dominservice\PayuMarketplace\Api\PayU;
use Dominservice\PayuMarketplace\Api\Util;
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;

/**
 * Class OpenPayU_Order
 */
class Order extends PayU
{
    const ORDER_SERVICE = 'orders/';
    const ORDER_TRANSACTION_SERVICE = 'transactions';
    const SUCCESS = 'SUCCESS';

    /**
     * @var array Default form parameters
     */
    protected static $defaultFormParams = array(
        'formClass' => '',
        'formId' => 'payu-payment-form',
        'submitClass' => '',
        'submitId' => '',
        'submitContent' => '',
        'submitTarget' => '_blank'
    );

    /**
     * Creates new Order
     * - Sends to PayU OrderCreateRequest
     *
     * @param array $order A array containing full Order
     * @return object $result Response array with OrderCreateResponse
     * @throws PayuMarketplaceException
     */
    public static function create($order)
    {
        $data = Util::buildJsonFromArray($order);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message OrderCreateRequest');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getServiceUrl() . self::ORDER_SERVICE;

        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'OrderCreateResponse');

        return $result;
    }

    /**
     * Retrieves information about the order
     *  - Sends to PayU OrderRetrieveRequest
     *
     * @param string $orderId PayU OrderId sent back in OrderCreateResponse
     * @return Api\Result $result Response array with OrderRetrieveResponse
     * @throws PayuMarketplaceException
     */
    public static function retrieve($orderId)
    {
        if (empty($orderId)) {
            throw new PayuMarketplaceException('Empty value of orderId');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getServiceUrl() . self::ORDER_SERVICE . $orderId;

        $result = self::verifyResponse(Http::doGet($pathUrl, $authType), 'OrderRetrieveResponse');

        return $result;
    }

    /**
     * Retrieves information about the order transaction
     *  - Sends to PayU TransactionRetrieveRequest
     *
     * @param string $orderId PayU OrderId sent back in OrderCreateResponse
     * @return Api\Result $result Response array with TransactionRetrieveResponse
     * @throws PayuMarketplaceException
     */
    public static function retrieveTransaction($orderId)
    {
        if (empty($orderId)) {
            throw new PayuMarketplaceException('Empty value of orderId');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getServiceUrl() . self::ORDER_SERVICE . $orderId . '/' . self::ORDER_TRANSACTION_SERVICE;

        $result = self::verifyResponse(Http::doGet($pathUrl, $authType), 'TransactionRetrieveResponse');

        return $result;
    }

    /**
     * Cancels Order
     * - Sends to PayU OrderCancelRequest
     *
     * @param string $orderId PayU OrderId sent back in OrderCreateResponse
     * @return Api\Result $result Response array with OrderCancelResponse
     * @throws PayuMarketplaceException
     */
    public static function cancel($orderId)
    {
        if (empty($orderId)) {
            throw new PayuMarketplaceException('Empty value of orderId');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getServiceUrl() . self::ORDER_SERVICE . $orderId;

        $result = self::verifyResponse(Http::doDelete($pathUrl, $authType), 'OrderCancelResponse');
        return $result;
    }

    /**
     * Updates Order status
     * - Sends to PayU OrderStatusUpdateRequest
     *
     * @param array $orderStatusUpdate A array containing full OrderStatus
     * @return OApi\Result $result Response array with OrderStatusUpdateResponse
     * @throws PayuMarketplaceException
     */
    public static function statusUpdate($orderStatusUpdate)
    {
        if (empty($orderStatusUpdate)) {
            throw new PayuMarketplaceException('Empty order status data');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $data = Util::buildJsonFromArray($orderStatusUpdate);
        $pathUrl = Configuration::getServiceUrl() . self::ORDER_SERVICE . $orderStatusUpdate['orderId'] . '/status';

        $result = self::verifyResponse(Http::doPut($pathUrl, $data, $authType), 'OrderStatusUpdateResponse');

        return $result;
    }

    /**
     * Consume notification message
     *
     * @access public
     * @param $data string Request array received from with PayU OrderNotifyRequest
     * @return null|Api\Result Response array with OrderNotifyRequest
     * @throws PayuMarketplaceException
     */
    public static function consumeNotification($data)
    {
        if (empty($data)) {
            throw new PayuMarketplaceException('Empty value of data');
        }

        $headers = Util::getRequestHeaders();
        $incomingSignature = HttpCurl::getSignature($headers);

        self::verifyDocumentSignature($data, $incomingSignature);

        return Order::verifyResponse(array('response' => $data, 'code' => 200), 'OrderNotifyRequest');
    }

    /**
     * Verify response from PayU
     *
     * @param $response
     * @param $messageName
     * @return Api\Result|void
     * @throws Exception\AuthException
     * @throws Exception\NetworkException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
     */
    public static function verifyResponse($response, $messageName)
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

        if ($httpStatus == 200 || $httpStatus == 201 || $httpStatus == 422 || $httpStatus == 301 || $httpStatus == 302) {
            return $result;
        }

        Http::throwHttpStatusException($httpStatus, $result);
    }

    /**
     * Generate a form body for hosted order
     *
     * @access public
     * @param array $order an array containing full Order
     * @param array $params an optional array with form elements' params
     * @return string Response html form
     * @throws ConfigException
     */
    public static function hostedOrderForm($order, $params = array())
    {
        $orderFormUrl = Configuration::getServiceUrl() . 'orders';

        $formFieldValuesAsArray = array();
        $htmlFormFields = Util::convertArrayToHtmlForm($order, '', $formFieldValuesAsArray);

        $signature = Util::generateSignData(
            $formFieldValuesAsArray,
            Configuration::getHashAlgorithm(),
            Configuration::getMerchantPosId(),
            Configuration::getSignatureKey()
        );

        $formParams = array_merge(self::$defaultFormParams, $params);

        $htmlOutput = sprintf("<form method=\"POST\" action=\"%s\" id=\"%s\" class=\"%s\">\n", $orderFormUrl, $formParams['formId'], $formParams['formClass']);
        $htmlOutput .= $htmlFormFields;
        $htmlOutput .= sprintf("<input type=\"hidden\" name=\"OpenPayu-Signature\" value=\"%s\" />", $signature);
        $htmlOutput .= sprintf("<button type=\"submit\" formtarget=\"%s\" id=\"%s\" class=\"%s\">%s</button>", $formParams['submitTarget'], $formParams['submitId'], $formParams['submitClass'], $formParams['submitContent']);
        $htmlOutput .= "</form>\n";

        return $htmlOutput;
    }
}
