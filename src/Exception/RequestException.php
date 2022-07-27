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

namespace Dominservice\PayuMarketplace\Exception;

class RequestException extends PayuMarketplaceException
{
    /** @var stdClass|null */
    private $originalResponseMessage;

    public function __construct($originalResponseMessage, $message = "", $code = 0, $previous = null)
    {
        $this->originalResponseMessage = $originalResponseMessage;

        parent::__construct($message, $code, $previous);
    }

    /** @return null|stdClass */
    public function getOriginalResponse()
    {
        return $this->originalResponseMessage;
    }
}