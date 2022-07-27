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

namespace Dominservice\PayuMarketplace\Api\Model;

class PayuShopBalance
{
    /** @var string */
    private $currencyCode;

    /** @var int */
    private $total;

    /** @var int */
    private $available;

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     * @return PayuShopBalance
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $total
     * @return PayuShopBalance
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return int
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @param int $available
     * @return PayuShopBalance
     */
    public function setAvailable($available)
    {
        $this->available = $available;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'PayuShopBalance [currencyCode=' . $this->currencyCode .
            ', total=' . $this->total .
            ', available=' . $this->available .
            ']';
    }
}
