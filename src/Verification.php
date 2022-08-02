<?php

namespace Dominservice\PayuMarketplace;

use Dominservice\PayuMarketplace\Api\Configuration;
use Dominservice\PayuMarketplace\Api\Http;
use Dominservice\PayuMarketplace\Api\PayU;
use Dominservice\PayuMarketplace\Api\Util;
use Dominservice\PayuMarketplace\Exception\AddressException;
use Dominservice\PayuMarketplace\Exception\AuthException;
use Dominservice\PayuMarketplace\Exception\CompanyException;
use Dominservice\PayuMarketplace\Exception\ContactException;
use Dominservice\PayuMarketplace\Exception\LegalFormException;
use Dominservice\PayuMarketplace\Exception\NetworkException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\Exception\PersonException;
use Dominservice\PayuMarketplace\Exception\RequestException;
use Dominservice\PayuMarketplace\Exception\SellerIdException;
use Dominservice\PayuMarketplace\Exception\ServerErrorException;
use Dominservice\PayuMarketplace\Exception\ServerMaintenanceException;
use Dominservice\PayuMarketplace\Exception\VerificationIdException;


class Verification extends PayU
{
    private $verificationId;
    private $sellerId;
    private $status;
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
     * Initializing Verification
     *
     * @param array $seller
     * @return object $result Response array with $seller InitializeVerificationResponse
     * @throws PayuMarketplaceException
     */
    public static function setSellerAssociates($associate)
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

        $pathUrl = Configuration::getSellerAssociatesEndpoint();
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'SellerAssociatesResponse');

        return $result;
    }

    /**
     * Initializing Verification
     *
     * @param array $seller
     * @return object $result Response array with $seller InitializeVerificationResponse
     * @throws PayuMarketplaceException
     */
    public static function setSellerFile($data, $filesize)
    {
//        $data = Util::buildJsonFromArray($file);

//        if (empty($data)) {
//            throw new PayuMarketplaceException('Empty message SellerFileResponse');
//        }

        try {
            $authType = self::getAuth();
            $authType->setHeader('Content-Type', 'multipart/form-data');
//            $authType->setHeader('Content-Length', $filesize);
        } catch (PayuMarketplaceException $e) {
            throw new PayuMarketplaceException($e->getMessage(), $e->getCode());
        }

        $pathUrl = Configuration::getSellerAssociatesEndpoint();
        $result = self::verifyResponse(Http::doPost($pathUrl, $data, $authType), 'SellerFileResponse');

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
// dump($data, $response, $httpStatus);
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




    public function getVerificationId()
    {
        return $this->verificationId;
    }

    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function sellerIsVerified()
    {
        return 'STATUS_'.$this->status === Api::STATUS_POSITIVE;
    }

    /**
     * @return bool
     */
    public function sellerIsNotVerified()
    {
        return 'STATUS_'.$this->status === Api::STATUS_NEGATIVE;
    }

    /**
     * @return bool
     */
    public function sellerIsWaiting()
    {
        return 'STATUS_'.$this->status === Api::STATUS_WAITING_FOR_DATA
            ||  'STATUS_'.$this->status === Api::STATUS_WAITING_FOR_VERIFICATION;
    }

    /**
     * @return bool
     */
    public function sellerIsWaitingForData()
    {
        return 'STATUS_'.$this->status === Api::STATUS_WAITING_FOR_DATA;
    }

    /**
     * @return bool
     */
    public function sellerIsWaitingForVerification()
    {
        return 'STATUS_'.$this->status === Api::STATUS_WAITING_FOR_VERIFICATION;
    }







}
