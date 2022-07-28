<?php

namespace Dominservice\PayuMarketplace;

use Dominservice\PayuMarketplace\Api\Configuration;
use Dominservice\PayuMarketplace\Api\Http;
use Dominservice\PayuMarketplace\Api\PayU;
use Dominservice\PayuMarketplace\Api\Util;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;


class Verification extends PayU
{
    private $companyName;
    private $name;
    private $surname;
    private $taxId;
    private $gusCode;
    private $legalForm;
    private $registryNumber;
    private $registrationDate;
    /**
     * @var array
     */
    private $address;
    private $email;
    private $phone;
    private $personalIdentificationNumber;
    private $dateOfBirth;




    /**
     * Checking Registration
     *
     * @param $identificationNumber
     * @return mixed|null
     */
    public static function verificationAdvice($identificationNumber)
    {
        try {
            $authType = self::getAuth();
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getVerificationAdviceEndpoint() . '/' . $identificationNumber;

        $result = self::verifyResponse(Http::doGet($pathUrl, $authType), 'VerificationAdviceResponse');

        return $result;
    }

    /**
     * Initializing Verification
     *
     * @param array $seller
     * @return object $result Response array with $seller InitializeVerificationResponse
     * @throws PayuMarketplaceException
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

    /**
     * Initializing Verification
     *
     * @param array $seller
     * @return object $result Response array with $seller InitializeVerificationResponse
     * @throws PayuMarketplaceException
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

        $pathUrl = Configuration::getSellerDataEndpoint();

        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'SellerDataResponse');

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
}
