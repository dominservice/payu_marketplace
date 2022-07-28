<?php

namespace Dominservice\PayuMarketplace;

use Dominservice\PayuMarketplace\Api\Configuration;
use Dominservice\PayuMarketplace\Api\PayU;
use Dominservice\PayuMarketplace\Exception\OrderException;
use Dominservice\PayuMarketplace\Exception\VerificationException;

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

    /**
     * @return Api\Result|object
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

    public function setCurrency($currency)
    {
        $this->orderCurrencyCode = $currency;

        return $this;
    }

    public function setDescription($description)
    {
        $this->orderDescription = $description;

        return $this;
    }

    /**
     * @param $id
     * @return Api\Result|false|mixed
     * @throws Exception\PayuMarketplaceException
     */
    public function checkSellerIsVerified($id)
    {
        if ($data = Verification::verificationAdvice($id)) {
//            $this->verificationId = !empty($data->verificationId) ? $data->verificationId : null;
//            $this->sellerId = !empty($data->sellerId) ? $data->sellerId : null;
//            $this->status = !empty($data->status) ? $data->status : null;

            return $data;
        }

        return false;
    }

    /**
     * @param $sellerId
     * @param $tyoe
     * @return Api\Result|false|object
     * @throws Exception\PayuMarketplaceException
     * @throws VerificationException
     */
    public function initializingVerification($sellerId, $tyoe = PayU::TYPE_FULL)
    {
        if (!in_array($tyoe, [PayU::TYPE_PAYOUT_ACCOUNT_DATA, PayU::TYPE_FULL, PayU::TYPE_UPDATE, PayU::TYPE_REVERIFICATION, PayU::TYPE_PERSONAL_ID_TAX_ID_CHANGE])) {
            throw new VerificationException("The address for notification is missing, add it using the setNotifyUrl() method");
        }

        $seller = [
            "sellerId" => $sellerId,
            "type" => $tyoe
        ];

        if ($data = Verification::initializingVerification($seller)) {
            return $data;
        }

        return false;
    }

    /**
     * @param $verificationId
     * @return $this
     */
    public function setVerificationId($verificationId)
    {
        $this->verificationId = $verificationId;

        return $this;
    }

    /**
     * @param $sellerId
     * @return $this
     */
    public function setSellerId($sellerId)
    {
        $this->sellerId = $sellerId;

        return $this;
    }

    /**
     * @param $companyName
     * @return $this
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param $surname
     * @return $this
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @param $taxId
     * @return $this
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;

        return $this;
    }

    /**
     * @param $legalForm
     * @return $this
     */
    public function setLegalForm($legalForm)
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    /**
     * Check information on this page
     * https://stat.gov.pl/metainformacje/slownik-pojec/pojecia-stosowane-w-statystyce-publicznej/97,pojecie.html
     *
     * @param $gusCode
     * @return $this
     */
    public function setGusCode($gusCode)
    {
        $this->gusCode = $gusCode;

        return $this;
    }

    /**
     * @param $registryNumber
     * @return $this
     */
    public function setRegistryNumber($registryNumber)
    {
        $this->registryNumber = $registryNumber;

        return $this;
    }

    /**
     * @param $registrationDate
     * @return $this
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * @param string $country
     * @param string|false $street
     * @param string|false $zipcode
     * @param string|false $city
     * @param bool $isAccountCloned
     * @return $this
     */
    public function setAddress($country, $street = false, $zipcode = false, $city = false, $isAccountCloned = false)
    {
        $this->address = ['country' => $country];

        if ($street) {
            $this->address['street'] = $street;
        }

        if ($zipcode) {
            $this->address['Zipcode'] = $zipcode;
        }

        if ($city) {
            $this->address['city'] = $city;
        }

        if ($isAccountCloned) {
//            $this->address['isAccountCloned'] = $isAccountCloned;
            $this->address['isAccountCloned'] = 'true';
        }

        return $this;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param $personalIdentificationNumber
     * @return $this
     */
    public function setPersonalIdentificationNumber($personalIdentificationNumber)
    {
        $this->personalIdentificationNumber = $personalIdentificationNumber;

        return $this;
    }

    /**
     * @param $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @param $typeAccount
     * @param $typeVerification
     * @return Api\Result|false|object
     * @throws Exception\PayuMarketplaceException
     * @throws VerificationException
     */
    public function setSellerData($typeAccount = 'company', $typeVerification = Api::TYPE_FULL)
    {
        if (empty($this->verificationId)) {
            throw new VerificationException("An empty 'verificationId' parameter must be provided to be able to query the API");
        }

        if (empty($this->sellerId)) {
            throw new VerificationException("An empty 'sellerId' parameter must be provided to be able to query the API");
        }

        $data = [
            'verificationId' => $this->verificationId,
            'sellerId' => $this->sellerId,
        ];

        if ($typeAccount === 'company') {
            if (empty($this->companyName)) {
                throw new VerificationException("An empty 'companyName' parameter must be provided to be able to query the API");
            } else {
                $data['companyName'] = $this->companyName;
            }
            if (empty($this->taxId)) {
                throw new VerificationException("An empty 'taxId' parameter must be provided to be able to query the API");
            } else {
                $data['taxId'] = $this->taxId;
            }
            if (!empty($this->gusCode)) {
                $data['gusCode'] = $this->gusCode;
            }
            if (!empty($this->registryNumber)) {
                $data['registryNumber'] = $this->registryNumber;
            }
            if (!empty($this->registrationDate)) {
                $data['registrationDate'] = $this->registrationDate;
            }
        } else {
            if (empty($this->name)) {
                throw new VerificationException("An empty 'name' parameter must be provided to be able to query the API");
            } else {
                $data['name'] = $this->name;
            }
            if (empty($this->surname)) {
                throw new VerificationException("An empty 'surname' parameter must be provided to be able to query the API");
            } else {
                $data['surname'] = $this->surname;
            }
            if ($typeVerification === Api::TYPE_FULL && empty($this->personalIdentificationNumber)) {
                throw new VerificationException("An empty 'personalIdentificationNumber' parameter must be provided to be able to query the API");
            } elseif (!empty($this->personalIdentificationNumber)) {
                $data['personalIdentificationNumber'] = $this->personalIdentificationNumber;
            }
            if (empty($this->dateOfBirth)) {
                throw new VerificationException("An empty 'dateOfBirth' parameter must be provided to be able to query the API");
            } else {
                $data['dateOfBirth'] = $this->dateOfBirth;
            }
        }

        if (empty($this->legalForm)) {
            throw new VerificationException("An empty 'legalForm' parameter must be provided to be able to query the API");
        } else {
            $data['legalForm'] = $this->legalForm;
        }

        if (empty($this->address)) {
            throw new VerificationException("An empty 'address' parameter must be provided to be able to query the API");
        } elseif (empty($this->address['country'])) {
            throw new VerificationException("An empty 'country' parameter in address must be provided to be able to query the API");
        } else {
            if ($typeVerification === Api::TYPE_FULL) {
                if (empty($this->address['street'])) {
                    throw new VerificationException("An empty 'street' parameter in address must be provided to be able to query the API");
                } elseif (empty($this->address['Zipcode'])) {
                    throw new VerificationException("An empty 'Zipcode' parameter in address must be provided to be able to query the API");
                } elseif (empty($this->address['city'])) {
                    throw new AddrVerificationExceptionessException("An empty 'city' parameter in address must be provided to be able to query the API");
                }
            }
            $data['address'] = $this->address;
        }

        if (empty($this->email)) {
            throw new VerificationException("An empty 'email' parameter must be provided to be able to query the API");
        } else {
            $data['email'] = $this->email;
        }

        if (empty($this->phone)) {
            throw new VerificationException("An empty 'phone' parameter must be provided to be able to query the API");
        } else {
            $data['phone'] = $this->phone;
        }

        if ($data = Verification::setSellerData($data)) {
            return $data;
        }

        return false;
    }

}
