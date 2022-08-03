<?php

namespace Dominservice\PayuMarketplace;

use Dominservice\PayuMarketplace\Api\Configuration;
use Dominservice\PayuMarketplace\Api\Order;
use Dominservice\PayuMarketplace\Exception\OrderException;

class Marketplace
{
    private $notifyUrl;
    private $continueUrl;
    private $products = [];
    private $sellerProducts = [];
    private $shippingMethods = [];
    private $sellerShippingMethods = [];
    private $sellerFee = [];
    private $orderCurrencyCode = [];
    /**
     * @var array
     */
    private $customer;
    private $orderDescription;

    /**
     * @param $orderId
     * @return Api\Result|object|null
     * @throws Exception\PayuMarketplaceException
     * @throws OrderException
     */
    public function createOrder($orderId)
    {
        $orderData = [
            'notifyUrl' => $this->getNotifyUrl(),
            'customerIp' => $this->getCustomerIp(),
            'merchantPosId' => Configuration::getMerchantPosId(),
            'totalAmount' => $this->calculateTotal(),
            'buyer' => $this->customer,
            'products' => $this->getProducts(),
            'shoppingCarts' => [],
            'extOrderId' => $orderId,
        ];

        if($this->getContinueUrl()) {
            $orderData['continueUrl'] = $this->getContinueUrl();
        }

        if (!empty($this->orderDescription)) {
            $orderData['description'] = $this->orderDescription;
        }
        if (!empty($this->orderCurrencyCode)) {
            $orderData['currencyCode'] = $this->orderCurrencyCode;
        }

        foreach ($this->sellerProducts as $sellerId => $sellerProducts) {
            $cart = [
                'extCustomerId' => $sellerId,
                'amount' => $this->calculateSellerTotal($sellerId),
                'fee' => $this->getSellerFee($sellerId, $this->calculateSellerTotal($sellerId)),
                'shippingMethods' => [],
                'products' => [],
            ];

            foreach ($sellerProducts as $sellerProduct) {
                $cart['products'][] = $this->products[$sellerProduct];
            }
            foreach ($this->sellerShippingMethods[$sellerId] as $sellerShippingMethod) {
                $cart['shippingMethods'][] = $this->shippingMethods[$sellerShippingMethod];
            }

            $orderData['shoppingCarts'][] = $cart;
        }

        return Order::create($orderData);
    }

    /**
     * @return mixed
     * @throws OrderException
     */
    private function getNotifyUrl()
    {
        if (!$this->notifyUrl) {
            throw new OrderException("The address for notification is missing, add it using the setNotifyUrl() method");
        }

        return $this->notifyUrl;
    }

    /**
     * @return mixed
     * @throws OrderException
     */
    private function getContinueUrl()
    {
        if (!$this->continueUrl) {
            throw new OrderException("The address for continue is missing, add it using the setContinueUrl() method");
        }

        return $this->continueUrl;
    }

    /**
     * @return mixed|string
     */
    private function getCustomerIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
           return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
           return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
           return $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
           return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
           return $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
           return $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
           return $_SERVER["HTTP_CF_CONNECTING_IP"];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
           return $_SERVER['REMOTE_ADDR'];
        } else {
           return 'UNKNOWN';
        }
    }

    /**
     * @return float|int|mixed
     */
    private function calculateTotal()
    {
        $total = 0;

        foreach ($this->products as $product) {
            $total += $product['unitPrice'] * $product['quantity'];
        }

        foreach ($this->shippingMethods as $shippingMethods) {
            $total += $shippingMethods['price'];
        }

        return $total;
    }

    /**
     * @param $sellerId
     * @return float|int|mixed
     */
    private function calculateSellerTotal($sellerId)
    {
        $total = 0;

        foreach ($this->sellerProducts[$sellerId] as $productId) {
            $total += $this->products[$productId]['unitPrice'] * $this->products[$productId]['quantity'];
        }

        foreach ($this->sellerShippingMethods[$sellerId] as $shippingMethodsId) {
            $total += $this->shippingMethods[$shippingMethodsId]['price'];
        }

        return $total;
    }

    /**
     * @return array
     */
    private function getProducts()
    {
        $products = [];

        foreach ($this->products as $product) {
            $products[] = [
                'name' => $product['name'],
                'unitPrice' => $product['unitPrice'],
                'quantity' => $product['quantity'],
            ];
        }

        return $products;
    }

    /**
     * @param $sellerId
     * @param $total
     * @return float|int|mixed
     */
    private function getSellerFee($sellerId, $total)
    {
        if ($this->sellerFee[$sellerId]['type'] === 'amount') {
            return $this->sellerFee[$sellerId]['fee'];
        } else {
            return (($total / 100) * $this->sellerFee[$sellerId]['fee']);
        }
    }

    /**
     * @param $url
     * @return $this
     */
    public function setNotifyUrl($url)
    {
        $this->notifyUrl = $url;
        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setContinueUrl($url)
    {
        $this->continueUrl = $url;
        return $this;
    }

    /**
     * @param $sellerId
     * @param $name
     * @param $unitPrice
     * @param $quantity
     * @param $listingDate
     * @param $virtual
     * @return $this
     */
    public function setProduct($sellerId, $name, $unitPrice, $quantity, $listingDate, $virtual = false)
    {
        $lastId = !empty($this->products) ? array_key_last($this->products) : 0;
        $this->products[$lastId+1] = [
            'name' => $name,
            'unitPrice' => (float)$unitPrice,
            'quantity' => (float)$quantity,
            'virtual' => $virtual,
            'listingDate' => $listingDate,
        ];
        $this->sellerProducts[$sellerId][] = $lastId+1;
        return $this;
    }

    /**
     * @param $sellerId
     * @param $country
     * @param $price
     * @param $name
     * @return $this
     */
    public function setShippingMethods($sellerId, $country, $price, $name)
    {
        $lastId = !empty($this->shippingMethods) ? array_key_last($this->shippingMethods) : 0;
        $this->shippingMethods[$lastId+1] = [
            'country' => $country,
            'price' => (float)$price,
            'name' => $name,
        ];
        $this->sellerShippingMethods[$sellerId][] = $lastId+1;
        return $this;
    }

    /**
     * @param $email
     * @param $phone
     * @param $firstName
     * @param $lastName
     * @param $languageCode
     * @return $this
     */
    public function setCustomer($email, $phone, $firstName, $lastName, $languageCode)
    {
        $this->customer = [
            'email' => $email,
            'phone' => $phone,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'language' => $languageCode,
        ];
        return $this;
    }

    /**
     * @param $sellerId
     * @param $fee
     * @param $type
     * @return $this
     * @throws OrderException
     */
    public function setSellerFee($sellerId, $fee, $type = 'percent')
    {
        if ($type === 'percent' && ($fee < 0 || $fee > 100)) {
            throw new OrderException("fee in percent should be in the range from 0 to 100");
        } elseif ($type === 'amount' && $fee < 0) {
            throw new OrderException("fee cannot be lower than zero and cannot be higher than the value of the cart of the selected seller");
        } elseif (!in_array($type, ['percent','amount'])) {
            throw new OrderException("type of fee must be 'percent' or 'amount'");
        }

        $this->sellerFee[$sellerId] = [
            'fee' => (float)$fee,
            'type' => $type,
        ];
        return $this;
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->orderCurrencyCode = $currency;

        return $this;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->orderDescription = $description;

        return $this;
    }

}
