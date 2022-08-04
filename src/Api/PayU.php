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

use Dominservice\PayuMarketplace\Api\Oauth\AuthType\Basic as AuthType_Basic;
use Dominservice\PayuMarketplace\Api\Oauth\AuthType\Oauth as AuthType_Oauth;
use Dominservice\PayuMarketplace\Exception\AuthException;

class PayU
{
    const TYPE_PAYOUT_ACCOUNT_DATA = 'PAYOUT_ACCOUNT_DATA';
    const TYPE_FULL = 'FULL';
    const TYPE_UPDATE = 'UPDATE';
    const TYPE_REVERIFICATION = 'REVERIFICATION';
    const TYPE_PERSONAL_ID_TAX_ID_CHANGE = 'PERSONAL_ID_TAX_ID_CHANGE';

    const PAYOUTS_TYPE_BANK_VTS = 'BANK_VTS'; // Verification transfer service
    const PAYOUTS_TYPE_BANK_NO_VTS = 'BANK_NO_VTS'; // Bank transfer without verification transfer service
    const PAYOUTS_TYPE_BANK_STATEMENT = 'BANK_STATEMENT'; // Bank statement

    const STATUS_WAITING_FOR_DATA = 'WAITING_FOR_DATA';
    const STATUS_WAITING_FOR_VERIFICATION = 'WAITING_FOR_VERIFICATION';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_POSITIVE = 'POSITIVE';
    const STATUS_NEGATIVE = 'NEGATIVE';

    const LEGAL_FORM_PRIVATE_PERSON = 'PRIVATE_PERSON';
    const LEGAL_FORM_SOLE_TRADER = 'SOLE_TRADER';
    const LEGAL_FORM_LEGAL_ENTITY = 'LEGAL_ENTITY'; // - only for foreign, non EOG companies
    const LEGAL_FORM_ASSOCIATION = 'ASSOCIATION';
    const LEGAL_FORM_CIVIL_LAW_PARTNERSHIP = 'CIVIL_LAW_PARTNERSHIP';
    const LEGAL_FORM_FOREIGN_COMPANY = 'FOREIGN_COMPANY';
    const LEGAL_FORM_FOUNDATION = 'FOUNDATION';
    const LEGAL_FORM_GENERAL_PARTNERSHIP = 'GENERAL_PARTNERSHIP';
    const LEGAL_FORM_JOINT_STOCK_COMPANY = 'JOINT_STOCK_COMPANY';
    const LEGAL_FORM_LIMITED_JOINT_STOCK_PARTNERSHIP = 'LIMITED_JOINT_STOCK_PARTNERSHIP';
    const LEGAL_FORM_LIMITED_LIABILITY_COMPANY = 'LIMITED_LIABILITY_COMPANY';
    const LEGAL_FORM_LIMITED_LIABILITY_PARTNERSHIP = 'LIMITED_LIABILITY_PARTNERSHIP';
    const LEGAL_FORM_PROFESSIONAL_PARTNERSHIP = 'PROFESSIONAL_PARTNERSHIP';
    const LEGAL_FORM_LIMITED_PARTNERSHIP = 'LIMITED_PARTNERSHIP';
    const LEGAL_FORM_OTHER = 'OTHER';

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
     * @return AuthType_Basic|AuthType_Oauth
     * @throws \Dominservice\PayuMarketplace\Exception\ConfigException
     * @throws \Dominservice\PayuMarketplace\Exception\PayuMarketplaceException
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
