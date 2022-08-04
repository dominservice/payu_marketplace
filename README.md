Library for PAYU Marketplace
=====

This library is based on "openpay/openpay"
The seller verification elements for the marketplace have been added, as well as the marketplace service itself.

## __The library is currently under construction, please check back, it will be ready soon.__

**The PAYU Marketplace PHP library provides integration access to the REST API 2.1**

## Dependencies
PHP >= 7.3 with extensions [cURL][ext1] and [hash][ext2]

## Documentation

Full implementation guide: [English Default][ext3] and [English Advanced][ext4]

Installing
==========

Add the dependency to your project:

```bash
composer require dominservice/payu_marketplace
```

Usage
=====

## Verification new seller

Verification type:
-  **PAYOUT_ACCOUNT_DATA** - initialized by marketplace side. The verification with
this type should contain the data of payout account and seller (submerchant). If this
verification initializes seller in PayU then seller data are required. If it is just account
update, then only account data are required.
-  **FULL** - verification which requires providing by seller all data for AML4 and payouts,
typically initialized by marketplace side.
-  **UPDATE** - verification typically initialized by merchant side, when seller
(submerchant) updates its data (eg. firm address). If merchant changes crucial data (eg.
Tax id, legal type) it is required to use FULL verification.
-  **REVERIFICATION** - verification typically initialized by PayU, when we require
merchant to update his data (eg. no longer valid document).
-  **PERSONAL_ID_TAX_ID_CHANGE** - initialized by marketplace side. This allows to
change PERSONAL_ID for private person or TAX_IDs for legal entity. Once the KYC
verification process begins, the current account and payouts are blocked until
verification is completed. If the process is interrupted (e.g. the company is unable to
verify itself), PayU restores the old merchant account, thus unlocking the payouts on it.

You can use the **\Dominservice\PayuMarketplace\Api\PayU** class to provide the correct type of verification

```php
    \Dominservice\PayuMarketplace\Api\PayU::TYPE_PAYOUT_ACCOUNT_DATA;
    \Dominservice\PayuMarketplace\Api\PayU::TYPE_FULL;
    \Dominservice\PayuMarketplace\Api\PayU::TYPE_UPDATE;
    \Dominservice\PayuMarketplace\Api\PayU::TYPE_REVERIFICATION;
    \Dominservice\PayuMarketplace\Api\PayU::TYPE_PERSONAL_ID_TAX_ID_CHANGE;
```
By default type is **FULL**,

## Getting started

If you are using Composer use autoload functionality:

```php
include "vendor/autoload.php";

use Dominservice\PayuMarketplace\Api\Configuration;
```
## Configure
**Important:** SDK works only with 'REST API' (Checkout) points of sales (POS).
If you do not already have PayU merchant account, [**please register in Production**][ext5] or [**please register in Sandbox**][ext6]

Example "Configuration keys" from Merchant Panel

![pos_configuration][img0] (used from openpayu/openpayu)

To configure OpenPayU environment you must provide a set of mandatory data in config.php file.

For production environment:
```php
    //set Production Environment
    Configuration::setEnvironment('secure');

    //set POS ID and Second MD5 Key (from merchant admin panel)
    Configuration::setMerchantPosId('145227');
    Configuration::setSignatureKey('13a980d4f851f3d9a1cfc792fb1f5e50');

    //set Oauth Client Id and Oauth Client Secret (from merchant admin panel)
    Configuration::setOauthClientId('145227');
    Configuration::setOauthClientSecret('12f071174cb7eb79d4aac5bc2f07563f'); 
```

For sandbox environment:
```php
    //set Sandbox Environment
    Configuration::setEnvironment('sandbox');

    //set POS ID and Second MD5 Key (from merchant admin panel)
    Configuration::setMerchantPosId('300046');
    Configuration::setSignatureKey('0c017495773278c50c7b35434017b2ca');

    //set Oauth Client Id and Oauth Client Secret (from merchant admin panel)
    Configuration::setOauthClientId('300046');
    Configuration::setOauthClientSecret('c8d4b7ac61758704f38ed5564d8c0ae0');
```
Set Cache directory:

```php
    use Dominservice\PayuMarketplace\Api\Oauth\OauthCacheFile;

    if (!file_exists(__DIR__ . '/payu_cache')) {
        @mkdir(__DIR__ . '/payu_cache', 0777);
    }
    
    Configuration::setOauthTokenCache(new OauthCacheFile(__DIR__ . '/payu_cache'));
```


# TEST

```php
use Dominservice\PayuMarketplace\Api\Oauth\OauthGrantType;
use Dominservice\PayuMarketplace\Api\PayU;
use Dominservice\PayuMarketplace\Exception\ConfigException;
use Dominservice\PayuMarketplace\Exception\PayuMarketplaceException;
use Dominservice\PayuMarketplace\Exception\VerificationException;
use Dominservice\PayuMarketplace\Marketplace;
use Dominservice\PayuMarketplace\Api\Verification;
use Dominservice\PayuMarketplace\Seller;

(...)

$sellerId = 'dso_seller_test';
$verificationId = null;
$verificationStatus = null;
$sellerInfo = (new Seller())->initializingVerification($sellerId);
$response = $sellerInfo->getResponse();

if (!empty($response->verificationId)) {
    $verificationId = $response->verificationId;
}
if (!empty($response->status)) {
    $verificationStatus = $response->status;
}

if ($verificationId && $verificationStatus === 'WAITING_FOR_DATA') {
    $sellerVerification = (new Seller())
        ->setAddress('PL', 'Zatylna 23/3', '00-001', 'Testowo')
        ->setVerificationId($verificationId)
        ->setSellerId($sellerId)
        ->setCompanyName('DSO-IT')
        ->setTaxId(6642146205)
        ->setLegalForm(PayU::LEGAL_FORM_SOLE_TRADER) // "legalForm value please place PRIVATE_PERSON or SOLE_TRADER."
        ->setGusCode('099') // https://stat.gov.pl/metainformacje/slownik-pojec/pojecia-stosowane-w-statystyce-publicznej/97,pojecie.html
        ->setRegistryNumber(123456789) // Polish REGON
        ->setRegistrationDate('2021-04-10')
        ->setEmail('biuro@dso.biz.pl')
        ->setPhone('555555555')
        ->setSellerData();

    if (!$sellerVerification->getError()) {
        $associateId = $this->uuid();
        $sellerAssociates = (new Seller())
            ->setVerificationId($verificationId)
            ->setAssociateId($associateId)
            ->setAssociateType('REPRESENTATIVE')
            ->setAssociateName('Tom')
            ->setAssociateSurname('Smith')
            ->setAssociateCitizenship('PL')
            ->setAssociateIdentityNumber('02052145584')
            ->setAssociateBirthDate('2002-01-02')
            ->setSellerAssociates();
            
        // $filename = $_FILES['file']['name'];
        // $filePath = $_FILES['file']['tmp_name'];
        // $filesize = $_FILES['file']['size'];
        $filename = 'przud';
        $filePath = __DIR__ . '/test_dowód_przud.jpg');
        $filesize = strlen(file_get_contents($filePath));
        $filename2 = 'tył';
        $filePath2 = __DIR__ . '/test_dowód_tył.jpg');
        $filesize2 = strlen(file_get_contents($filePath2));

        if (function_exists('curl_file_create')) { // php 5.5+
            $cFile = curl_file_create($filePath);
            $cFile2 = curl_file_create($filePath2);
        } else {
            $cFile = '@' . realpath($filePath);
            $cFile2 = '@' . realpath($filePath2);
        }
        
        $sellerFiles = [];
        $sellerFile = (new Seller())
            ->setVerificationId($verificationId)
            ->setSellerFile($filename, $cFile, $filesize);
        }
        
        if ($fileResponse = $sellerFile->getResponse()) {
            $sellerFiles[$filename] = $fileResponse->fileId;
        }
        
        $sellerFile = (new Seller())
            ->setVerificationId($verificationId)
            ->setSellerFile($filename2, $cFile2, $filesize);
        }
        
        if ($fileResponse = $sellerFile->getResponse()) {
            $sellerFiles[$filename2] = $fileResponse->fileId;
        }
        
        if (!empty($sellerFiles)) {
            $sellerDocumentId = $this->uuid();
            $sellerDocument = (new Seller())
                ->setVerificationId($verificationId)
                ->setDocumentId($sellerDocumentId)
                ->setDocumentType('REGISTRY_DOCUMENT')
                ->setFile($sellerFiles[$filename])
                ->setFile($sellerFiles[$filename2])
                ->setSellerDocuments();
                
            $associateDocumentId = $this->uuid();
            $associateDocument = (new Seller())
                ->setVerificationId($verificationId)
                ->setAssociateId($associateId)
                ->setDocumentId($associateDocumentId)
                ->setDocumentType('RESIDENCE_PERMIT')
                ->setDocumentNumber('QWE 111111')
                ->setFile($sellerFiles[$filename])
                ->setFile($sellerFiles[$filename2])
                ->setIssueDate('2022-01-02')
                ->setExpireDate('2032-01-02')
                ->setAssociateDocuments();
        }
    }



//        $order = (new Marketplace())
//            ->setNotifyUrl(route('payu_test_notify'))
//            ->setCustomer(
//                'biuro@dso.biz.pl',
//                '555555555',
//                'Tester',
//                'Oblatywacz',
//                'pl'
//            )
//            ->setSellerFee($sellerId, 10)
//            ->setProduct($sellerId, 'Produkt testowy', 23, 5, date('Y-m-d'))
//            ->setShippingMethods($sellerId, 'PL', 12, 'Przewożnik A')
//            ->setCurrency('PLN')
//            ->setDescription('bla bla blaS')
//            ->createOrder(time());
```

<!--external links:-->
[ext1]: http://php.net/manual/en/book.curl.php
[ext2]: http://php.net/manual/en/book.hash.php
[ext3]: http://developers.payu.com/en/
[ext4]: http://developers.payu.com/pl/
[ext5]: https://www.payu.pl/en/commercial-offer
[ext6]: https://secure.snd.payu.com/boarding/#/form&pk_campaign=Plugin-Github&pk_kwd=SDK

<!--images:-->
[img0]: readme_images/pos_configuration.png
