<?php

namespace Dominservice\PayuMarketplace\Api;

use Dominservice\PayuMarketplace\Api\Oauth\OauthCacheInterface;
use Dominservice\PayuMarketplace\Api\Oauth\OauthGrantType;
use Dominservice\PayuMarketplace\Exception\ConfigException;

class Configuration
{
    private static $_availableEnvironment = array('custom', 'secure', 'sandbox');
    private static $_availableHashAlgorithm = array('SHA', 'SHA-256', 'SHA-384', 'SHA-512');

    private static $env = 'secure';

    /**
     * Merchant Pos ID for Auth Basic and Notification Consume
     */
    private static $merchantPosId = '';

    /**
     * Signature Key for Auth Basic and Notification Consume
     */
    private static $signatureKey = '';

    /**
     * OAuth protocol - default type
     */
    private static $oauthGrantType = OauthGrantType::CLIENT_CREDENTIAL;
    /**
     * OAuth protocol - client_id
     */
    private static $oauthClientId = '';

    /**
     * OAuth protocol - client_secret
     */
    private static $oauthClientSecret = '';

    /**
     * OAuth protocol - email
     */
    private static $oauthEmail = '';

    /**
     * OAuth protocol - extCustomerId
     */
    private static $oauthExtCustomerId;

    /**
     * OAuth protocol - endpoint address
     */
    private static $oauthEndpoint = '';

    /**
     * Verification protocol - endpoint address
     */
    private static $verificationEndpoint = '';

    /**
     * Verification advice protocol - endpoint address
     */
    private static $verificationAdviceEndpoint = '';

    /**
     * Verification dataloading protocol - endpoint address
     */
    private static $dataloadingEndpoint = '';

    /**
     * OAuth protocol - methods for token cache
     */
    private static $oauthTokenCache = null;

    /**
     * Proxy - host
     */
    private static $proxyHost = null;

    /**
     * Proxy - port
     */
    private static $proxyPort = null;

    /**
     * Proxy - user
     */
    private static $proxyUser = null;

    /**
     * Proxy - password
     */
    private static $proxyPassword = null;

    private static $serviceUrl = '';
    private static $hashAlgorithm = 'SHA-256';

    private static $sender = 'Generic';

    const API_VERSION = '2.1';
    const COMPOSER_JSON = "/composer.json";
    const DEFAULT_SDK_VERSION = 'PHP SDK 1.0.0';
    const OAUTH_CONTEXT = 'pl/standard/user/oauth/authorize';

    const AML_VERIFICATION_CONTEXT = 'api/aml-verification/v1/';
    const VERIVICATION_CONTEXT = 'verification';
    const VERIVICATION_ADVICE_CONTEXT = 'verification-advice';
    const DATALOADING_CONTEXT = 'dataloading';

    /**
     * @return string
     */
    public static function getApiVersion()
    {
        return self::API_VERSION;
    }

    /**
     * @param $value
     * @return void
     * @throws ConfigException
     */
    public static function setHashAlgorithm($value)
    {
        if (!in_array($value, self::$_availableHashAlgorithm)) {
            throw new ConfigException('Hash algorithm "' . $value . '"" is not available');
        }

        self::$hashAlgorithm = $value;
    }

    /**
     * @return string
     */
    public static function getHashAlgorithm()
    {
        return self::$hashAlgorithm;
    }

    /**
     * @param $environment
     * @param $domain
     * @param $api
     * @param $version
     * @return void
     * @throws ConfigException
     */
    public static function setEnvironment($environment = 'secure', $domain = 'payu.com', $api = 'api/', $version = 'v2_1/')
    {
        $environment = strtolower($environment);
        $domain = strtolower($domain) . '/';

        if (!in_array($environment, self::$_availableEnvironment)) {
            throw new ConfigException($environment . ' - is not valid environment');
        }

        self::$env = $environment;

        if ($environment == 'secure') {
            $domain = 'https://' . $environment . '.' . $domain;
        } elseif ($environment == 'sandbox') {
            $domain = 'https://secure.snd.' . $domain;
        }

        self::$serviceUrl = $domain . $api . $version;
        self::$oauthEndpoint = $domain . self::OAUTH_CONTEXT;
        self::$verificationEndpoint = $domain . self::AML_VERIFICATION_CONTEXT . self::VERIVICATION_CONTEXT;
        self::$verificationAdviceEndpoint = $domain . self::AML_VERIFICATION_CONTEXT . self::VERIVICATION_ADVICE_CONTEXT;
        self::$dataloadingEndpoint = $domain . self::AML_VERIFICATION_CONTEXT . self::DATALOADING_CONTEXT;
    }

    /**
     * @return string
     */
    public static function getServiceUrl()
    {
        return self::$serviceUrl;
    }

    /**
     * @return string
     */
    public static function getOauthEndpoint()
    {
        return self::$oauthEndpoint;
    }

    /**
     * @return string
     */
    public static function getVerificationEndpoint()
    {
        return self::$verificationEndpoint;
    }

    /**
     * @return string
     */
    public static function getVerificationAdviceEndpoint()
    {
        return self::$verificationAdviceEndpoint;
    }

    /**
     * @return string
     */
    public static function getDataloadingEndpoint()
    {
        return self::$dataloadingEndpoint;
    }

    /**
     * @return string
     */
    public static function getEnvironment()
    {
        return self::$env;
    }

    /**
     * @param string
     */
    public static function setMerchantPosId($value)
    {
        self::$merchantPosId = trim($value);
    }

    /**
     * @return string
     */
    public static function getMerchantPosId()
    {
        return self::$merchantPosId;
    }

    /**
     * @param string
     */
    public static function setSignatureKey($value)
    {
        self::$signatureKey = trim($value);
    }

    /**
     * @return string
     */
    public static function getSignatureKey()
    {
        return self::$signatureKey;
    }

    /**
     * @return string
     */
    public static function getOauthGrantType()
    {
        return self::$oauthGrantType;
    }

    /**
     * @param $oauthGrantType
     * @return void
     * @throws ConfigException
     */
    public static function setOauthGrantType($oauthGrantType)
    {
        if ($oauthGrantType !== OauthGrantType::CLIENT_CREDENTIAL && $oauthGrantType !== OauthGrantType::TRUSTED_MERCHANT) {
            throw new ConfigException('Oauth grand type "' . $oauthGrantType . '"" is not available');
        }

        self::$oauthGrantType = $oauthGrantType;
    }

    /**
     * @return string
     */
    public static function getOauthClientId()
    {
        return self::$oauthClientId;
    }

    /**
     * @return string
     */
    public static function getOauthClientSecret()
    {
        return self::$oauthClientSecret;
    }

    /**
     * @param mixed $oauthClientId
     */
    public static function setOauthClientId($oauthClientId)
    {
        self::$oauthClientId = trim($oauthClientId);
    }

    /**
     * @param mixed $oauthClientSecret
     */
    public static function setOauthClientSecret($oauthClientSecret)
    {
        self::$oauthClientSecret = trim($oauthClientSecret);
    }

    /**
     * @return mixed
     */
    public static function getOauthEmail()
    {
        return self::$oauthEmail;
    }

    /**
     * @param mixed $oauthEmail
     */
    public static function setOauthEmail($oauthEmail)
    {
        self::$oauthEmail = $oauthEmail;
    }

    /**
     * @return mixed
     */
    public static function getOauthExtCustomerId()
    {
        return self::$oauthExtCustomerId;
    }

    /**
     * @param mixed $oauthExtCustomerId
     */
    public static function setOauthExtCustomerId($oauthExtCustomerId)
    {
        self::$oauthExtCustomerId = $oauthExtCustomerId;
    }

    /**
     * @return null | OauthCacheInterface
     */
    public static function getOauthTokenCache()
    {
        return self::$oauthTokenCache;
    }

    /**
     * @param OauthCacheInterface $oauthTokenCache
     * @throws ConfigException
     */
    public static function setOauthTokenCache($oauthTokenCache)
    {
        if (!$oauthTokenCache instanceof OauthCacheInterface) {
            throw new ConfigException('Oauth token cache class is not instance of OauthCacheInterface');
        }
        self::$oauthTokenCache = $oauthTokenCache;
    }

    /**
     * @return string | null
     */
    public static function getProxyHost()
    {
        return self::$proxyHost;
    }

    /**
     * @param string | null $proxyHost
     */
    public static function setProxyHost($proxyHost)
    {
        self::$proxyHost = $proxyHost;
    }

    /**
     * @return int | null
     */
    public static function getProxyPort()
    {
        return self::$proxyPort;
    }

    /**
     * @param int | null $proxyPort
     */
    public static function setProxyPort($proxyPort)
    {
        self::$proxyPort = $proxyPort;
    }

    /**
     * @return string | null
     */
    public static function getProxyUser()
    {
        return self::$proxyUser;
    }

    /**
     * @param string | null $proxyUser
     */
    public static function setProxyUser($proxyUser)
    {
        self::$proxyUser = $proxyUser;
    }

    /**
     * @return string | null
     */
    public static function getProxyPassword()
    {
        return self::$proxyPassword;
    }

    /**
     * @param string | null $proxyPassword
     */
    public static function setProxyPassword($proxyPassword)
    {
        self::$proxyPassword = $proxyPassword;
    }

    /**
     * @param string $sender
     */
    public static function setSender($sender)
    {
        self::$sender = $sender;
    }

    /**
     * @return string
     */
    public static function getSender()
    {
        return self::$sender;
    }

    /**
     * @return string
     */
    public static function getFullSenderName()
    {
        return sprintf("%s@%s", self::getSender(), self::getSdkVersion());
    }

    /**
     * @return string
     */
    public static function getSdkVersion()
    {
        $composerFilePath = self::getComposerFilePath();
        if (file_exists($composerFilePath)) {
            $fileContent = file_get_contents($composerFilePath);
            $composerData = json_decode($fileContent);
            if (isset($composerData->version) && isset($composerData->extra[0]->engine)) {
                return sprintf("%s %s", $composerData->extra[0]->engine, $composerData->version);
            }
        }

        return self::DEFAULT_SDK_VERSION;
    }

    /**
     * @return string
     */
    private static function getComposerFilePath()
    {
        return realpath(dirname(__FILE__)) . '/../vendor/' . self::COMPOSER_JSON;
    }
}
