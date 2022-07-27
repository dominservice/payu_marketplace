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
use Dominservice\PayuMarketplace\AuthType\Oauth as AuthType_Oauth;
use Dominservice\PayuMarketplace\AuthType\Basic as AuthType_Basic;

class PayU
{
    protected static function build($data)
    {
        $instance = new Result();

        if (array_key_exists('status', $data) && $data['status'] == 'WARNING_CONTINUE_REDIRECT') {
            $data['status'] = 'SUCCESS';
            $data['response']['status']['statusCode'] = 'SUCCESS';
        }

        $instance->init($data);

        return $instance;
    }

    /**
     * @param $data
     * @param $incomingSignature
     * @return void
     * @throws AuthException
     */
    public static function verifyDocumentSignature($data, $incomingSignature)
    {
        $sign = Util::parseSignature($incomingSignature);

        if ($sign === null || !array_key_exists('signature', $sign) || !array_key_exists('algorithm', $sign)) {
            throw new AuthException('Signature not found');
        }

        if (false === Util::verifySignature(
                $data,
                $sign['signature'],
                Configuration::getSignatureKey(),
                $sign['algorithm'])
        ) {
            throw new AuthException('Invalid signature - ' . $sign['signature']);
        }
    }

    /**
     * @return AuthType
     * @throws OpenPayU_Exception
     */
    protected static function getAuth()
    {
        if (Configuration::getOauthClientId() && Configuration::getOauthClientSecret()) {
            $authType = new AuthType_Oauth(Configuration::getOauthClientId(), Configuration::getOauthClientSecret());
        } else {
            $authType = new AuthType_Basic(Configuration::getMerchantPosId(), Configuration::getSignatureKey());
        }

        return $authType;
    }


}