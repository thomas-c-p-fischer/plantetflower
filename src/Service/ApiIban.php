<?php

namespace App\Service;

use App\Service\MockStorage;
use App\Entity\User;
use App\Service\ApiUser;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use MangoPay;

class ApiIban
{
    private $mangoPayApi;
    private $client;

    public function __construct(ApiUser $apiUser, HttpClientInterface $httpClient)
    {
        $this->client = $httpClient;
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        // $this->mangoPayApi->Config->TemporaryFolder = 'mangotemp';
        $this->mangoPayApi->OAuthTokenManager->RegisterCustomStorageStrategy(new MockStorage());
        // $this->mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com/';
    }

    public function NewIban($iban, $bic, $user)
    {
        $BankAccount = new \MangoPay\BankAccount();
        $BankAccount->Type = 'IBAN';
        $BankAccount->Details = new \MangoPay\BankAccountDetailsIBAN();
        $BankAccount->Details->IBAN = $iban;
        $BankAccount->Details->BIC = $bic;
        $BankAccount->OwnerName = $user->getFirstName() . ' ' . $user->getLastName();
        $BankAccount->OwnerAddress = new \MangoPay\Address();
        $BankAccount->OwnerAddress->AddressLine1 = $user->getStreetNumber() . ' ' . $user->getAddress();
        $BankAccount->OwnerAddress->City = $user->getCity();
        $BankAccount->OwnerAddress->PostalCode = $user->getZipCode();
        $BankAccount->OwnerAddress->Country = 'FR';

        $ResultBankAccount = $this->mangoPayApi->Users->CreateBankAccount($user->getIdMangopay(), $BankAccount);
        return $ResultBankAccount;
    }

    public function GetListBankAccount($UserId)
    {
        $response = $this->client->request(
            'GET',
            'https://api.sandbox.mangopay.com/v2.01/' . $_ENV['CLIENT_ID'] . '/users' . '/' . $UserId . '/bankaccounts' . '/',
            [
                'auth_basic' => [
                    $_ENV['CLIENT_ID'], // username
                    $_ENV['API_KEY']    //password
                ],
                'body' => [
                    'UserId' => $UserId,
                    'Page' => 1,
                    'Per_Page' => 25,
                    'Sort' => "CreationDate:DESC",
                    'Active' => true
                ]
            ]
        );

        $content = $response->toArray();

        return $content;
    }

    public function GetBankAccount($user, $form)
    {
        $ListBank = $this->GetListBankAccount($user);
        $BankAccount = array_search($form['IBAN']->getData(), $ListBank);
        return $BankAccount;
    }

    public function GetLastBankAccount($UserId)
    {
        $ListBank = $this->GetListBankAccount($UserId);
        $LastBankAccount = $ListBank['0'];

        return $LastBankAccount;
    }
}