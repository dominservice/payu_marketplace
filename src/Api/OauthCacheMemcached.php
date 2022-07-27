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

use Dominservice\PayuMarketplace\Exception\ConfigException;

class OauthCacheMemcached implements OauthCacheInterface
{
    private $memcached;

    /**
     * @param $host
     * @param $port
     * @param $weight
     * @throws ConfigException
     */
    public function __construct($host = 'localhost', $port = 11211, $weight = 0)
    {
        if (!class_exists('Memcached')) {
            throw new ConfigException('PHP Memcached extension not installed.');
        }

        $this->memcached = new Memcached('PayU');
        $this->memcached->addServer($host, $port, $weight);
        $stats = $this->memcached->getStats();
        if ($stats[$host . ':' . $port]['pid'] == -1) {
            throw new ConfigException('Problem with connection to memcached server [host=' . $host . '] [port=' . $port . '] [weight=' . $weight . ']');
        }
    }

    public function get($key)
    {
        $cache = $this->memcached->get($key);
        return $cache === false ? null : unserialize($cache);
    }

    public function set($key, $value)
    {
        return $this->memcached->set($key, serialize($value));
    }

    public function invalidate($key)
    {
        return $this->memcached->delete($key);
    }

}
