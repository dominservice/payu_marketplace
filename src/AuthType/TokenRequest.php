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

namespace Dominservice\PayuMarketplace\AuthType;

class TokenRequest implements AuthType
{
    private $headers = array(
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: */*'
    );

    public function setHeader($key, $val)
    {
        $this->headers[$key] = $val;
        return $this;
    }

    public function getHeaders()
    {
        $headers = [];

        foreach ($this->headers as $key=>$val) {
            $headers[] = $key . ': ' . $val;
        }

        return $headers;
    }
}
