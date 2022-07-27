<?php

namespace Dominservice\PayuMarketplace;

use Dominservice\PayuMarketplace\Exception\AddressException;
use Dominservice\PayuMarketplace\Exception\CompanyException;
use Dominservice\PayuMarketplace\Exception\ContactException;
use Dominservice\PayuMarketplace\Exception\LegalFormException;
use Dominservice\PayuMarketplace\Exception\PersonException;
use Dominservice\PayuMarketplace\Exception\SellerIdException;
use Dominservice\PayuMarketplace\Exception\VerificationIdException;


class Verification extends Api
{
    private $verificationId;
    private $sellerId;
    private $status;
    private $companyName;
    private $name;
    private $surname;
    private $taxId;
    private $gusCode;
    private $legalForm;
    private $registryNumber;
    private $registrationDate;
    /**
     * @var array
     */
    private $address;
    private $email;
    private $phone;
    private $personalIdentificationNumber;
    private $dateOfBirth;

    /**
     * @param $client_id
     * @param $client_secret
     */
    public function __construct($client_id, $client_secret, $enviroment = 'secure', $access_token = null)
    {
        parent::__construct($client_id, $client_secret, $enviroment, $access_token);

        $this->auth();
    }

    /**
     * @param $taxId
     * @return bool
     */
    public function checkCompanyIsVerified($taxId)
    {
        if ($data = $this->verificationAdvice($taxId)) {
            $this->verificationId = !empty($data->verificationId) ? $data->verificationId : null;
            $this->sellerId = !empty($data->sellerId) ? $data->sellerId : null;
            $this->status = !empty($data->status) ? $data->status : null;

            return true;
        }

        return false;
    }

    /**
     * @param $personalIdentificationNumber
     * @return bool
     */
    public function checkPersonIsVerified($personalIdentificationNumber)
    {
        if ($data = $this->verificationAdvice($personalIdentificationNumber)) {
            $this->verificationId = !empty($data->verificationId) ? $data->verificationId : null;
            $this->sellerId = !empty($data->sellerId) ? $data->sellerId : null;
            $this->status = !empty($data->status) ? $data->status : null;

            return true;
        }

        return false;

    }

    /**
     * @param $personalIdentificationNumber
     * @return bool
     */
    public function checkSellerVerified($personalIdentificationNumber)
    {
        if ($data = $this->verificationAdvice($personalIdentificationNumber)) {
            $this->verificationId = !empty($data->verificationId) ? $data->verificationId : null;
            $this->sellerId = !empty($data->sellerId) ? $data->sellerId : null;
            $this->status = !empty($data->status) ? $data->status : null;

            return true;
        }

        return false;

    }

    public function getVerificationId()
    {
        return $this->verificationId;
    }

    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function sellerIsVerified()
    {
        return 'STATUS_'.$this->status === Api::STATUS_POSITIVE;
    }

    /**
     * @return bool
     */
    public function sellerIsNotVerified()
    {
        return 'STATUS_'.$this->status === Api::STATUS_NEGATIVE;
    }

    /**
     * @return bool
     */
    public function sellerIsWaiting()
    {
        return 'STATUS_'.$this->status === Api::STATUS_WAITING_FOR_DATA
            ||  'STATUS_'.$this->status === Api::STATUS_WAITING_FOR_VERIFICATION;
    }

    /**
     * @return bool
     */
    public function sellerIsWaitingForData()
    {
        return 'STATUS_'.$this->status === Api::STATUS_WAITING_FOR_DATA;
    }

    /**
     * @return bool
     */
    public function sellerIsWaitingForVerification()
    {
        return 'STATUS_'.$this->status === Api::STATUS_WAITING_FOR_VERIFICATION;
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
            $this->address['zipcode'] = $zipcode;
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
     * @return void
     * @throws AddressException
     * @throws CompanyException
     * @throws ContactException
     * @throws LegalFormException
     * @throws PersonException
     * @throws SellerIdException
     * @throws VerificationIdException
     */
    public function setDataToVerification($typeAccount = 'company', $typeVerification = Api::TYPE_FULL)
    {
        if (empty($this->verificationId)) {
            throw new VerificationIdException("An empty 'verificationId' parameter must be provided to be able to query the API");
        }

        if (empty($this->sellerId)) {
            throw new SellerIdException("An empty 'sellerId' parameter must be provided to be able to query the API");
        }

        $data = [
            'verificationId' => $this->verificationId,
            'sellerId' => $this->sellerId,
        ];

        if ($typeAccount === 'company') {
            if (empty($this->companyName)) {
                throw new CompanyException("An empty 'companyName' parameter must be provided to be able to query the API");
            } else {
                $data['companyName'] = $this->companyName;
            }
            if (empty($this->taxId)) {
                throw new CompanyException("An empty 'taxId' parameter must be provided to be able to query the API");
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
                throw new PersonException("An empty 'name' parameter must be provided to be able to query the API");
            } else {
                $data['name'] = $this->name;
            }
            if (empty($this->surname)) {
                throw new PersonException("An empty 'surname' parameter must be provided to be able to query the API");
            } else {
                $data['surname'] = $this->surname;
            }
            if ($typeVerification === Api::TYPE_FULL && empty($this->personalIdentificationNumber)) {
                throw new PersonException("An empty 'personalIdentificationNumber' parameter must be provided to be able to query the API");
            } elseif (!empty($this->personalIdentificationNumber)) {
                $data['personalIdentificationNumber'] = $this->personalIdentificationNumber;
            }
            if (empty($this->dateOfBirth)) {
                throw new PersonException("An empty 'dateOfBirth' parameter must be provided to be able to query the API");
            } else {
                $data['dateOfBirth'] = $this->dateOfBirth;
            }
        }

        if (empty($this->legalForm)) {
            throw new LegalFormException("An empty 'legalForm' parameter must be provided to be able to query the API");
        } else {
            $data['legalForm'] = $this->legalForm;
        }

        if (empty($this->address)) {
            throw new AddressException("An empty 'address' parameter must be provided to be able to query the API");
        } elseif (empty($this->address['country'])) {
            throw new AddressException("An empty 'country' parameter in address must be provided to be able to query the API");
        } else {
            if ($typeVerification === Api::TYPE_FULL) {
                if (empty($this->address['street'])) {
                    throw new AddressException("An empty 'street' parameter in address must be provided to be able to query the API");
                } elseif (empty($this->address['Zipcode'])) {
                    throw new AddressException("An empty 'Zipcode' parameter in address must be provided to be able to query the API");
                } elseif (empty($this->address['city'])) {
                    throw new AddressException("An empty 'city' parameter in address must be provided to be able to query the API");
                }
            }
            $data['address'] = $this->address;
        }

        if (empty($this->email)) {
            throw new ContactException("An empty 'email' parameter must be provided to be able to query the API");
        } else {
            $data['email'] = $this->email;
        }

        if (empty($this->phone)) {
            throw new ContactException("An empty 'phone' parameter must be provided to be able to query the API");
        } else {
            $data['phone'] = $this->phone;
        }



    }

}
