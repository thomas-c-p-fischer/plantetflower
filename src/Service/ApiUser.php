<?php

namespace App\Service;

use MangoPay;
use App\Entity\User;
use App\Service\MockStorage;
use MangoPay\CardRegistration;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiUser
{
    private $client;
    private $mangoPayApi;
    public $userMangoPay;


    public function __construct(HttpClientInterface $httpClient)
    {
        $this->client = $httpClient;
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        // $this->mangoPayApi->Config->TemporaryFolder = 'mangotemp';
        $this->mangoPayApi->OAuthTokenManager->RegisterCustomStorageStrategy(new MockStorage());
        // $this->mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com/';
    }

    public function NewProfil($form)
    {

        $date = date_format($form['birthday']->getData(), "Ymd");
        $timestampDate = strtotime($date);

        if ($form['streetnumber']) {
            $address = $form['streetnumber']->getData() . ' ' . $form['address']->getData() . ' ' . $form['address2']->getData();
        } else {
            $address = $form['address']->getData() . ' ' . $form['address2']->getData();
        }

        $UserNatural = new MangoPay\UserNatural();
        $UserNatural->FirstName = $form['firstname']->getData();
        $UserNatural->LastName = $form['lastname']->getData();
        $UserNatural->Email = $form['email']->getData();
        $UserNatural->Address = new MangoPay\Address();
        $UserNatural->Address->AddressLine1 = $address;
        $UserNatural->Address->City = $form['city']->getData();
        $UserNatural->Address->PostalCode = $form['zipcode']->getData();
        $UserNatural->Address->Region = '';
        $UserNatural->Address->Country = 'FR';
        $UserNatural->Birthday = $timestampDate;
        $UserNatural->Nationality = $form['nationality']->getData();
        $UserNatural->CountryOfResidence = 'FR';
        $UserNatural->Capacity = 'NORMAL';
        $UserNatural->Tag = 'Create a user from Plant et Flower';

        $ResultUserMango = $this->mangoPayApi->Users->Create($UserNatural);
        $this->userMangoPay = $ResultUserMango;
        return $ResultUserMango;
    }

    public function GetProfil($user)
    {
        $id = $user->getIdMangopay();
        $User = $this->mangoPayApi->Users->Get($id);
        $this->userMangoPay = $User;
        return $User;
    }

    public function EditProfil($user, $form)
    {
        $date = date_format($user->getBirthday(), "Ymd");
        $timestampDate = strtotime($date);

        $UserNatural = new \MangoPay\UserNatural();
        $UserNatural->Id = $user->getIdMangoPay();
        $UserNatural->FirstName = $form['firstname']->getData();
        $UserNatural->LastName = $form['lastname']->getData();
        $UserNatural->Email = $form['email']->getData();
        $UserNatural->Nationality = $user->getNationality(); // $user['nationality']
        $UserNatural->CountryOfResidence = "FR";
        $UserNatural->Birthday = $timestampDate;
        $UserNatural->Address = new \MangoPay\Address();
        $UserNatural->Address->AddressLine1 = $form['streetnumber']->getData() . ' ' . $form['address']->getData() . ' ' . $form['address2']->getData();
        $UserNatural->Address->City = $form['city']->getData();
        $UserNatural->Address->Region = "";
        $UserNatural->Address->PostalCode = $form['zipcode']->getData();
        $UserNatural->Address->Country = "FR";
        $Result = $this->mangoPayApi->Users->Update($UserNatural);

        return $Result;
    }

    public function Registration($UserId)
    {
        $CardRegistration = new \MangoPay\CardRegistration();
        $CardRegistration->UserId = $UserId;
        $CardRegistration->Currency = "EUR";
        $CardRegistration->CardType = "CB_VISA_MASTERCARD";
        $Result = $this->mangoPayApi->CardRegistrations->Create($CardRegistration);
        $this->CardRegistrationUrl = $Result->CardRegistrationURL;

        return $Result;
    }

    public function Data($Registration, $Form)
    {
        $response = $this->client->request(
            'POST',
            $Registration->CardRegistrationURL,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => [
                    'data' => $Registration->PreregistrationData,
                    'accessKeyRef' => $Registration->AccessKey,
                    'cardNumber' => $Form['cardNumber']->getData(),
                    'cardExpirationDate' => $Form['cardExpirationDate']->getData(),
                    'cardCvx' => $Form['cardCvx']->getData(),
                ]
            ]
        );

        return $response;
    }

    public function RegistrationCard($CardId, $Data)
    {
        $cardRegister = $this->mangoPayApi->CardRegistrations->Get($CardId);
        $cardRegister->RegistrationData = $Data;

        $updatedCardRegister = $this->mangoPayApi->CardRegistrations->Update($cardRegister);

        return $updatedCardRegister;
    }

    public function getCardInfo()
    {
        $getCardRegistration = $this->mangoPayApi->CardRegistrations->Get($this->registration->Id);

        return $getCardRegistration;
    }

    public function GetCardByNumber($UserId, $CardNumber)
    {
        $start = 6;
        $end = 11;

        $ArrayNumber = str_split($CardNumber);

        for ($i = $start; $i <= $end; $i++) {
            $ArrayNumber[$i] = "X";
        }

        $EncryptedCard = implode(", ", $ArrayNumber);
        $EncryptedCard = str_replace(', ', '', $EncryptedCard);

        $startPage = 1;
        $endPage = 2;

        for ($i = $startPage; $i <= $endPage; $i++) {
            $response = $this->client->request(
                'GET',
                'https://api.sandbox.mangopay.com/v2.01/' . $_ENV['CLIENT_ID'] . '/users' . '/' . $UserId . '/cards?page=' . $i . '&per_page=20',
                [
                    'auth_basic' => [
                        $_ENV['CLIENT_ID'], // username
                        $_ENV['API_KEY']    //password
                    ]
                ]
            );

            $content = $response->toArray();

            if (sizeOf($content) > 0) {
                foreach ($content as $card) {
                    if ($EncryptedCard === $card['Alias']) {
                        if ("VALID" === $card["Validity"]) {
                            if (true === $card["Active"]) {
                                return $card;
                            }
                        }
                    } else {
                        $startPage += 2;
                        $endPage += 2;
                    }
                }
            } else {
                return false;
            }
        }
    }

    public function GetUserById($Id)
    {
        // dd('https://api.sandbox.mangopay.com/v2.01/' . $_ENV['CLIENT_ID'] . '/users' . '/' . $Id);
        $response = $this->client->request(
            'GET',
            'https://api.sandbox.mangopay.com/v2.01/' . $_ENV['CLIENT_ID'] . '/users' . '/' . $Id,
            [
                'auth_basic' => [
                    $_ENV['CLIENT_ID'], // username
                    $_ENV['API_KEY']    //password
                ]
            ]
        );
        // dd($response->toArray());
        $content = $response->toArray();

        return $content;
    }

    public function GetUserByEmail($Email)
    {
        $start = 1;
        $end = 2;

        // boucle qui changera le 'start' et 'end s'il trouvera pas le mail qui corrisponde
        for ($i = $start; $i <= $end; $i++) {
            $response = $this->client->request(
                'GET',
                'https://api.sandbox.mangopay.com/v2.01/' . $_ENV['CLIENT_ID'] . '/users?page=' . $i . '&per_page=20',
                [
                    'auth_basic' => [
                        $_ENV['CLIENT_ID'], // username
                        $_ENV['API_KEY']    //password
                    ]
                ]
            );
            $content = $response->toArray();

            if (sizeOf($content) > 0) {
                foreach ($content as $user) {
                    if ($Email == $user['Email']) {
                        return $user;
                    } else {
                        $start += 2;
                        $end += 2;
                    }
                }
            } else {
                return false;
            }
        }
    }
}

