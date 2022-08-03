# payu_marketplace
Library for PAYU Marketplace

This library is based on "openpay/openpay"
The seller verification elements for the marketplace have been added, as well as the marketplace service itself.

__The library is currently under construction, please check back, it will be ready soon.__

Installing
==========

Add the dependency to your project:

```bash
composer require dominservice/payu_marketplace
```

Usage
=====

# TEST

```php
use Dominservice\PayuMarketplace\Api\Configuration;
use Dominservice\PayuMarketplace\Api\Oauth\OauthCacheFile;
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
        //set Sandbox Environment
        Configuration::setEnvironment(config('payu.environment'));

        //set POS ID and Second MD5 Key (from merchant admin panel)
        Configuration::setMerchantPosId(config('payu.pos_id'));
        Configuration::setSignatureKey(config('payu.second_client_secret'));

        //set Oauth Client Id and Oauth Client Secret (from merchant admin panel)
        Configuration::setOauthClientId(config('payu.client_id'));
        Configuration::setOauthClientSecret(config('payu.client_secret'));

        if (!file_exists(storage_path('payu_cache'))) {
            @mkdir(storage_path('payu_cache'), 0777);
        }
        
        Configuration::setOauthTokenCache(new OauthCacheFile(storage_path('payu_cache')));

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
                ->setLegalForm(PayU::LEGAL_FORM_OTHER)
                ->setGusCode('099')
                ->setRegistryNumber(388558595)
                ->setRegistrationDate('2021-04-10')
                ->setEmail('biuro@dso.biz.pl')
                ->setPhone('666605081')
                ->setSellerData();


            dump($sellerVerification);

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


                dump($sellerAssociates);

//                $filename = $_FILES['file']['name'];
//                $filePath = $_FILES['file']['tmp_name'];
//                $filesize = $_FILES['file']['size'];

                $filename = 'testowy';
//                $filePath = 'https://d-art.ppstatic.pl/kadry/k/r/1/03/26/60c9e8421cbdc_o_full.jpg';
                $filePath2 = storage_path('app/payu/test_dowód.jpg');

                $filesize = strlen(file_get_contents($filePath2));

                if (function_exists('curl_file_create')) { // php 5.5+
                    $cFile = curl_file_create($filePath2);
                } else { //
                    $cFile = '@' . realpath($filePath2);
                }

                dump([$cFile, $filePath2, '@' . realpath($filePath2)]);


                $sellerFile = [];
                $sellerFile[] = (new Seller())
                    ->setVerificationId($verificationId)
                    ->setSellerFile($filename, $cFile, $filesize);

                dump($sellerFile);
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