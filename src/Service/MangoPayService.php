<?php

namespace App\Service;

use App\Entity\User;
use MangoPay;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\Wallet;


class MangoPayService
{
    private MangoPay\MangoPayApi $mangoPayApi;
    private $apiUsers;

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
        if ($user->getAgreeTerms()) {
            $newUser->TermsAndConditionsAccepted = true;
        }
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

//Methode permettant d'ajouter un compte bancaire au compte mangoPay associé.
    public function createBankAccount(User $user, $iban)
    {
        $idMangoPay = $user->getIdMangopay();
        $bankAccount = new MangoPay\BankAccount();
        $bankAccount->Type = 'IBAN';
        $bankAccount->Details = new BankAccountDetailsIBAN();
        $bankAccount->Details->IBAN = $iban;
        $bankAccount->OwnerAddress = new MangoPay\Address();
        $bankAccount->OwnerAddress->AddressLine1 = $user->getAddress();
        $bankAccount->OwnerAddress->PostalCode = $user->getZipCode();
        $bankAccount->OwnerAddress->City = $user->getCity();
        $bankAccount->OwnerAddress->Country = $user->getCountryOfResidence();
        $bankAccount->UserId = $idMangoPay;
        $bankAccount->OwnerName = $user->getLastName() . ' ' . $user->getFirstName();

        return $this->mangoPayApi->Users->CreateBankAccount($idMangoPay, $bankAccount);

    }

    //Methode permentant d'ajouter un KYC created.
    public function createKYCDocument(User $user, $document)
    {
        $mangoPayIdUser = $user->getIdMangopay();
        $KYC = new MangoPay\KycDocument();
        $KYC->UserId = $mangoPayIdUser;
        $KYC->Type = $document;

        return $this->mangoPayApi->Users->CreateKycDocument($mangoPayIdUser, $KYC);
    }

}
