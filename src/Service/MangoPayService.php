<?php

namespace App\Service;

use App\Entity\User;
use MangoPay;
use MangoPay\Wallet;


class MangoPayService
{
    private MangoPay\MangoPayApi $mangoPayApi;

    public function __construct()
    {
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $this->mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
        $this->mangoPayApi->Config->TemporaryFolder = 'C:\Users\mauri\PhpstormProjects\plantetflower\public';

    }

//Methode permettant de creer un utlisateur sur MangoPay
    public function createNaturalUser($firstName, $lastName, $email)
    {
        $newUser = new \MangoPay\UserNatural();
        $newUser->Email = $email;
        $newUser->FirstName = $firstName;
        $newUser->LastName = $lastName;
        $newUser->Birthday = 121271;
        $newUser->Nationality = 'FR';
        $newUser->CountryOfResidence = "FR";
        $result = $this->mangoPayApi->Users->Create($newUser);
        return $result->Id;
    }


    public function recupIdMangoPay()
    {
        $idNaturalUser = new MangoPay\UserNatural();

        return $idNaturalUser->Id;
    }

//Methode permettant de creer un wallet a un natural user
    public function createWalletForNaturalUser($naturalUserId)
    {
        $Wallet = new Wallet();
        $Wallet->Owners = array($naturalUserId);
        $Wallet->Description = "Adrien Wallet";
        $Wallet->Currency = "EUR";
        $result = $this->mangoPayApi->Wallets->Create($Wallet);
        return $result->Id;
    }
}