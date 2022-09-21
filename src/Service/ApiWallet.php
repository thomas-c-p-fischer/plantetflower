<?php

namespace App\Service;

use App\Service\MockStorage;
use App\Entity\User;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use MangoPay;

class ApiWallet
{
    private $mangoPayApi;
    private $client;

    public $walletMangoPay;

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

    public function NewWallet($userId): MangoPay\Wallet
    {
        $Wallet = new \MangoPay\Wallet();
        $Wallet->Owners = array($userId);
        $Wallet->Description = "A wallet";
        $Wallet->Currency = "EUR";
        $Wallet->Tag = "Create a wallet from Plant et Flower";

        $ResultWallet = $this->mangoPayApi->Wallets->Create($Wallet);
        $this->walletMangoPay = $ResultWallet;
        return $ResultWallet;
    }

    public function GetWallet($MangoPayIdUser)
    {
        $response = $this->client->request(
            'GET',
            'https://api.sandbox.mangopay.com/v2.01/' . $_ENV['CLIENT_ID'] . '/users' . '/' . $MangoPayIdUser . '/wallets' . '/',
            [
                'auth_basic' => [
                    $_ENV['CLIENT_ID'], // username
                    $_ENV['API_KEY']    //password
                ],
            ]
        );

        $Wallets = $response->toArray();
        $Wallet = $Wallets[0];

        return $Wallet;
    }
}