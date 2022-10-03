<?php

namespace App\Service;

use App\Entity\User;
use MangoPay;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\Wallet;
use Symfony\Component\Mime\Address;


class MangoPayService
{
    private MangoPay\MangoPayApi $mangoPayApi;
    private MangoPay\ApiUsers $apiUsers;

    public function __construct()
    {
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $this->mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
        $this->mangoPayApi->Config->TemporaryFolder = $_ENV['TMP_PATH'];
    }

//Méthode permettant de créer un utilisateur sur MangoPay
    public function createNaturalUser(User $user)
    {
        $mangoPayApi = $this->mangoPayApi;
        $newUser = new \MangoPay\UserNatural();
        $newUser->Email = $user->getEmail();
        $newUser->FirstName = $user->getFirstName();
        $newUser->LastName = $user->getLastName();
        $newUser->Birthday = $user->getBirthday()->getTimestamp();
        $newUser->Nationality = $user->getNationality();
        $newUser->Address = new MangoPay\Address();
        $newUser->Address->AddressLine1 = $user->getAddress();
        $newUser->Address->AddressLine2 = $user->getAddress2();
        $newUser->Address->PostalCode = $user->getZipCode();
        $newUser->Address->City = $user->getCity();
        $newUser->Address->Country = $user->getCountryOfResidence();
        $newUser->Nationality = $user->getNationality();
        $newUser->CountryOfResidence = $user->getCountryOfResidence();
        if ($user->isOwner()) {
            $newUser->UserCategory = "Owner";
        } else {
            $newUser->UserCategory = "Payer";
        }
        $result = $mangoPayApi->Users->Create($newUser);
        $idMangoPay = $result->Id;
        $user->setIdMangopay($idMangoPay);
        return $result->Id;
    }

//Méthode permettant de créer un wallet à un natural user
    public function createWalletForNaturalUser($naturalUserId, User $user)
    {

        $Wallet = new Wallet();
        $Wallet->Owners = array($naturalUserId);
        $Wallet->Description = $user->getFirstName() . ' ' . $user->getLastName() . ' wallet' . '.';
        $Wallet->Currency = "EUR";
        $result = $this->mangoPayApi->Wallets->Create($Wallet);
        $idWallet = $result->Id;
        $user->setidWallet($idWallet);
        return $result->Id;
    }

    public function createBankAccount($idUserMangoPay, $iban)
    {
        $bankAccount = new MangoPay\BankAccount();
        $bankAccount->Details = new BankAccountDetailsIBAN();
        $bankAccount->Details->IBAN = $iban;
        $result = $this->mangoPayApi->Users->CreateBankAccount($idUserMangoPay, $bankAccount);
        return $result->Id;
    }
}
