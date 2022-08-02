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

class HttpCurl
{
    /**
     * @var
     */
    static $headers;

    /**
     * @param $requestType
     * @param $pathUrl
     * @param $auth
     * @param $data
     * @return array
     * @throws \Dominservice\PayuMarketplace\Exception\ConfigException
     * @throws \Dominservice\PayuMarketplace\Exception\NetworkException
     */
    public static function doPayuRequest($requestType, $pathUrl, $auth, $data = null)
    {
        if (empty($pathUrl)) {
            throw new \Dominservice\PayuMarketplace\Exception\ConfigException('The endpoint is empty');
        }

        $ch = curl_init($pathUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $auth->getHeaders());
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'OpenPayU_HttpCurl::readHeader');
        if ($data) {
            if (!empty($data['__file_size__'])) {
                curl_setopt($ch, CURLOPT_INFILESIZE, $data['__file_size__']);
                curl_setopt($ch, CURLOPT_HTTP_VERSION,  CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE);


                dump($auth->getHeaders());

                unset($data['__file_size__']);
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        if ($proxy = self::getProxy()) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
            if ($proxyAuth = self::getProxyAuth()) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
            }
        }

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($response === false) {
            throw new \Dominservice\PayuMarketplace\Exception\NetworkException(curl_error($ch));
        }
        curl_close($ch);

        return array('code' => $httpStatus, 'response' => trim($response));
    }

    /**
     * @param array $headers
     *
     * @return mixed
     */
    public static function getSignature($headers)
    {
        foreach($headers as $name => $value)
        {
            if(preg_match('/X-OpenPayU-Signature/i', $name) || preg_match('/OpenPayu-Signature/i', $name))
                return $value;
        }

        return null;
    }

    /**
     * @param resource $ch
     * @param string $header
     * @return int
     */
    public static function readHeader($ch, $header)
    {
        if( preg_match('/([^:]+): (.+)/m', $header, $match) ) {
            self::$headers[$match[1]] = trim($match[2]);
        }

        return strlen($header);
    }

    private static function getProxy()
    {
        return Configuration::getProxyHost() != null ? ':' . Configuration::getProxyPort() : false;
    }

    private static function getProxyAuth()
    {
        return Configuration::getProxyUser() != null ? ':' . Configuration::getProxyPassword() : false;
    }

}
