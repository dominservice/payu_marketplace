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

use Dominservice\PayuMarketplace\Exception\ConfigException;

class OauthCacheFile implements OauthCacheInterface
{
    private $directory;

    /**
     * @param $directory
     * @throws ConfigException
     */
    public function __construct($directory = null)
    {
        if ($directory === null) {
            $directory = dirname(__FILE__) . '/../Cache';
        }

        if (!is_dir($directory) || !is_writable($directory)) {
            throw new ConfigException('Cache directory [' . $directory . '] not exist or not writable.');
        }

        $this->directory = $directory . (substr($directory, -1) != '/' ? '/' : '');
    }

    public function get($key)
    {
        $cache = @file_get_contents($this->directory . md5($key));
        return $cache === false ? null : unserialize($cache);
    }

    public function set($key, $value)
    {
        return @file_put_contents($this->directory . md5($key), serialize($value));
    }

    public function invalidate($key)
    {
        return @unlink($this->directory . md5($key));
    }

}