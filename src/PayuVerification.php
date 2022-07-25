<?php

namespace Dominservice\PayuMarketplace;

class PayuVerification extends Api
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

    public function __construct($client_id, $client_secret)
    {
        parent::__construct($client_id, $client_secret);

        $this->auth();
    }

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
        return 'STATUS_'.$this->status === Verification::STATUS_POSITIVE;
    }

    /**
     * @return bool
     */
    public function sellerIsNotVerified()
    {
        return 'STATUS_'.$this->status === Verification::STATUS_NEGATIVE;
    }

    /**
     * @return bool
     */
    public function sellerIsWaiting()
    {
        return 'STATUS_'.$this->status === Verification::STATUS_WAITING_FOR_DATA
            ||  'STATUS_'.$this->status === Verification::STATUS_WAITING_FOR_VERIFICATION;
    }

    /**
     * @return bool
     */
    public function sellerIsWaitingForData()
    {
        return 'STATUS_'.$this->status === Verification::STATUS_WAITING_FOR_DATA;
    }

    /**
     * @return bool
     */
    public function sellerIsWaitingForVerification()
    {
        return 'STATUS_'.$this->status === Verification::STATUS_WAITING_FOR_VERIFICATION;
    }


    public function setDataToVerification($typeAccount = 'company', $typeVerification = Verification::TYPE_FULL)
    {

    }

    public function setVerificationId($verificationId)
    {
        $this->verificationId = $verificationId;

        return $this;
    }

    public function setSellerId($sellerId)
    {
        $this->sellerId = $sellerId;

        return $this;
    }

    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function setLegalForm($legalForm)
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function setGusCode($gusCode)
    {
        $this->gusCode = $gusCode;

        return $this;
    }

    public function setRegistryNumber($registryNumber)
    {
        $this->registryNumber = $registryNumber;

        return $this;
    }

    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function setAddress($street, $zipcode, $city, $country, $isAccountCloned = false)
    {
        $this->address = [
            'street' => $street,
            'Zipcode' => $zipcode,
            'city' => $city,
            'country' => $country,
            'isAccountCloned' => $isAccountCloned,
        ];

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function setPersonalIdentificationNumber($personalIdentificationNumber)
    {
        $this->personalIdentificationNumber = $personalIdentificationNumber;

        return $this;
    }

    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

}