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

use Dominservice\PayuMarketplace\Exception\ConfigException;

class Basic implements AuthType
{

    /**
     * @var string
     */
    private $authBasicToken;

    public function __construct($posId, $signatureKey)
    {
        if (empty($posId)) {
            throw new ConfigException('PosId is empty');
        }

        if (empty($signatureKey)) {
            throw new ConfigException('SignatureKey is empty');
        }

        $this->authBasicToken = base64_encode($posId . ':' . $signatureKey);

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $this->authBasicToken,
        ];
    }

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
