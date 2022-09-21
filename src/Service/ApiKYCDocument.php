<?php

namespace App\Service;


use Symfony\Contracts\HttpClient\HttpClientInterface;
use MangoPay;

class ApiKYCDocument
{
    private MangoPay\MangoPayApi $mangoPayApi;

    public function __construct(HttpClientInterface $httpClient,
    )
    {
        $this->client = $httpClient;
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $this->mangoPayApi->OAuthTokenManager->RegisterCustomStorageStrategy(new MockStorage());
    }

    public function NewKYCDocument($userId, $recto, $verso)
    {

        // create kyc document
        $kycDocument = new \MangoPay\KycDocument();
        $kycDocument->Status = \MangoPay\KycDocumentStatus::Created;
        $kycDocument->Type = "IDENTITY_PROOF";
        $createdKycDocument = $this->mangoPayApi->Users->CreateKycDocument($userId, $kycDocument);

        $getKycDocument = $this->mangoPayApi->Users->GetKycDocument($userId, $createdKycDocument->Id);
        // create kyc page
        $KycPage = new \MangoPay\KycPage();
        $KycPage->File = $recto;
        $KycPage->File .= $verso;
        $KYCDocumentId = $getKycDocument->Id;
        $RectoPage = $this->mangoPayApi->Users->CreateKycPageFromFile($userId, $KYCDocumentId, $recto);
        $VersoPage = $this->mangoPayApi->Users->CreateKycPageFromFile($userId, $KYCDocumentId, $verso);
        if ($RectoPage && $VersoPage) {
            $ResultPage = true;
        } else {
            $ResultPage = false;
        }

        return $KYCDocumentId;
    }

    public function SubmitKYCDocument($UserId, $KYCDocId)
    {
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Id = $KYCDocId;
        $KycDocument->Status = \MangoPay\KycDocumentStatus::ValidationAsked; // VALIDATION_ASKED
        $Result = $this->mangoPayApi->Users->UpdateKycDocument($UserId, $KycDocument);

        return $Result;
    }

    public function GetKYCDocumentById($IdUser)
    {
        $url = 'https://api.sandbox.mangopay.com/v2.01/' . $_ENV['CLIENT_ID'] . '/users' . '/' . $IdUser . '/kyc/documents?per_page=100';

        $response = $this->client->request(
            'GET',
            $url,
            [
                'auth_basic' => [
                    $_ENV['CLIENT_ID'], // username
                    $_ENV['API_KEY']    //password
                ]
            ]
        );

        $content = $response->toArray();

        $mostRecent = 0;
        $now = time();

        if (sizeof($content) > 0) {
            foreach ($content as $KycDoc) {
                if ($KycDoc["CreationDate"] > $mostRecent && $KycDoc["CreationDate"] < $now) {
                    $mostRecent = $KycDoc["CreationDate"];
                }
            }

            foreach ($content as $KycDoc) {
                if ($KycDoc["CreationDate"] === $mostRecent) {
                    return $KycDoc;
                }
            }
        } else {
            return false;
        }
    }
}