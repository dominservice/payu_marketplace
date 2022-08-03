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

namespace Dominservice\PayuMarketplace\Api\Oauth;

interface OauthCacheInterface
{

    /**
     * @param string $key
     * @return null | object
     */
    public function get($key);

    /**
     * @param string $key
     * @param object $value
     * @return bool
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @return bool
     */
    public function invalidate($key);

}