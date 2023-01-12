<?php

namespace Dominservice\PayuMarketplace\Api;

use Dominservice\PayuMarketplace\Api;
use Dominservice\PayuMarketplace\Exception;
use Dominservice\PayuMarketplace\Exception\AuthException;
use Dominservice\PayuMarketplace\Exception\NetworkException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\Exception\RequestException;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;
use Dominservice\PayuMarketplace\Exception\ServerMaintenanceException;


class Verification extends PayU
{
    /**
     * @param $identificationNumber
     * @return Result|null
     * @throws AuthException
     * @throws Exception\ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function verificationAdvice($identificationNumber)
    {
        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getVerificationAdviceEndpoint() . '/' . $identificationNumber;
        $result = self::verifyResponse(Http::doGet($pathUrl, $authType), 'VerificationAdviceResponse', 'GET');

        return $result;
    }

    /**
     * @param $seller
     * @return Result|null
     * @throws AuthException
     * @throws Exception\ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function initializingVerification($seller)
    {
        $data = Util::buildJsonFromArray($seller);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message InitializeVerificationResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getVerificationEndpoint();
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'InitializeVerificationResponse');

        return $result;
    }

    public static function getVerification($verificationId)
    {
        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getVerificationEndpoint() . '?id=' . $verificationId;
        $result = self::verifyResponse(Http::doGet($pathUrl, $authType), 'VerificationResponse', 'GET');

        return $result;
    }

    /**
     * @param $seller
     * @return Result|null
     * @throws AuthException
     * @throws Exception\ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function setSellerData($seller)
    {
        $data = Util::buildJsonFromArray($seller);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message SellerDataResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getDataloadingEndpoint() . '/seller';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'SellerDataResponse');

        return $result;
    }

    /**
     * @param $associate
     * @return Result|null
     * @throws AuthException
     * @throws Exception\ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function setAssociates($associate)
    {
        $data = Util::buildJsonFromArray($associate);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message SellerDataResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getDataloadingEndpoint() . '/associates';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'SellerAssociatesResponse');

        return $result;
    }

    /**
     * @param $data
     * @return Result|null
     * @throws AuthException
     * @throws Exception\ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function setFile($data)
    {
        try {
            $authType = self::getAuth();
            $authType->setHeader('Content-Type', 'multipart/form-data');
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getDataloadingEndpoint() . '/files';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'SellerFileResponse');

        return $result;
    }

    /**
     * @param $documents
     * @return Result|null
     * @throws AuthException
     * @throws Exception\ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function setSellerDocuments($documents)
    {
        $data = Util::buildJsonFromArray($documents);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message SellerDocumentsResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getDataloadingEndpoint() . '/seller/documents';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'SellerDocumentsResponse');

        return $result;
    }

    /**
     * @param $documents
     * @return Result|null
     * @throws AuthException
     * @throws Exception\ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function setAssociatesDocuments($documents)
    {
        $data = Util::buildJsonFromArray($documents);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message AssociatesDocumentsResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getDataloadingEndpoint() . '/associates/documents';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'AssociatesDocumentsResponse');

        return $result;
    }

    /**
     * @param $documents
     * @return Result|null
     * @throws AuthException
     * @throws Exception\ConfigException
     * @throws NetworkException
     * @throws PayuMarketplaceException
     * @throws RequestException
     * @throws ServerErrorException
     * @throws ServerMaintenanceException
     */
    public static function setPayoutDetails($documents)
    {
        $data = Util::buildJsonFromArray($documents);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message PayoutDetailsResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getDataloadingEndpoint() . '/payouts/bankAccountData';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'PayoutDetailsResponse');

        return $result;
    }

    public static function setVerificationTransferManual($transfer)
    {
        $data = Util::buildJsonFromArray($transfer);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message VerificationTransferManualResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getVerificationTransferEndpoint() . '/manual';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'VerificationTransferManualResponse');

        return $result;
    }

    public static function setPayoneer($payoneer)
    {
        $data = Util::buildJsonFromArray($payoneer);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message PayoneerResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getDataloadingEndpoint() . '/payouts/payoneer';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'PayoneerResponse');

        return $result;
    }

    public static function setComplete($complete)
    {
        $data = Util::buildJsonFromArray($complete);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message CompleteResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getVerificationEndpoint() . '/complete';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'CompleteResponse');

        return $result;
    }

    public static function setCancel($cancel)
    {
        $data = Util::buildJsonFromArray($cancel);

        if (empty($data)) {
            throw new PayuMarketplaceException('Empty message CancelResponse');
        }

        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getVerificationEndpoint() . '/cancel';
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'CancelResponse');

        return $result;
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
    public static function verifyResponse($response, $messageName, $requestType = null)
    {
        $data = array();
        if ($requestType === 'GET') {
            $httpStatus = $response['code'];
            $data['status'] = null;
            $data['response'] = $response;
        } else {
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
        }

        $result = self::build($data);

        if ($httpStatus == 200 || $httpStatus == 201 || $httpStatus == 204 || $httpStatus == 422 || $httpStatus == 301 || $httpStatus == 302) {
            if ($httpStatus == 204) {
                $result->setSuccess(1);
            }
            return $result;
        }

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
