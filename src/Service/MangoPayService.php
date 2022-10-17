<?php

namespace App\Service;

use App\Entity\Annonce;
use App\Entity\User;
use Exception;
use MangoPay;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\CardRegistration;
use MangoPay\Wallet;

class MangoPayService
{
    private MangoPay\MangoPayApi $mangoPayApi;

    //Constructeur qui sert à l'initialisation de l'api.
    // Les "$_ENV" sont les éléments à compléter dans le .env.local.
    public function __construct()
    {
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $this->mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
        $this->mangoPayApi->Config->TemporaryFolder = $_ENV['TMP_PATH'];

    }

//Méthode permettant de créer un utilisateur sur MangoPay
    public function createNaturalUser(User $user): ?string
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
    public function createWalletForNaturalUser($naturalUserId, User $user): ?string
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
    public function createBankAccount(User $user, $iban): MangoPay\BankAccount
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

    //Methode permettant d'ajouter un KYC created.
    public function createKYCDocument(User $user, $document): ?string
    {
        $mangoPayIdUser = $user->getIdMangopay();
        $KYC = new MangoPay\KycDocument();
        $KYC->UserId = $mangoPayIdUser;
        $KYC->Type = $document;

        $result = $this->mangoPayApi->Users->CreateKycDocument($mangoPayIdUser, $KYC);
        return $result->Id;
    }


    //Methode pour créer un KYC et lui attribuer le statut "CREATED" indispensable pour la suite.
    public function createKYCPage(User $user, $KYCDocumentId, $recto, $verso)
    {
        $userId = $user->getIdMangopay();
        $KYCPage = new \MangoPay\KycPage();
        $KYCPage->File = $recto;
        $KYCPage->File .= $verso;

        $this->mangoPayApi->Users->CreateKycPageFromFile($userId, $KYCDocumentId, $recto);
        $this->mangoPayApi->Users->CreateKycPageFromFile($userId, $KYCDocumentId, $verso);

        return $KYCDocumentId;
    }

    //Cette methode est reliée à celle du dessus. Lorsque l'utilisateur insérer ses pieces justificative
    // lors de la creation, la methode ci-dessous
    //Envoie ces derniers à mangoPay et passe sous le statut "VALIDATION ASKED".
    // MangoPay se charge de les verifier et de les accepter s'ils sont valides.
    public function submitKYCDocument(User $user, $KYCDocId): MangoPay\KycDocument
    {
        $userId = $user->getIdMangopay();
        $KYCDocument = new \MangoPay\KycDocument();
        $KYCDocument->Id = $KYCDocId;
        $KYCDocument->Status = \MangoPay\KycDocumentStatus::ValidationAsked; // VALIDATION_ASKED
        return $this->mangoPayApi->Users->UpdateKycDocument($userId, $KYCDocument);
    }

    //Methode de Thomas.
    public function createCardRegistration(User $user)
    {
        try {
            $userId = $user->getIdMangopay();
            $cardRegistration = new CardRegistration();
            $cardRegistration->UserId = $userId;
            $cardRegistration->Currency = 'EUR';
            $cardRegistration->CardType = 'CB_VISA_MASTERCARD';
            $createdCardRegister = $this->mangoPayApi->CardRegistrations->Create($cardRegistration);
            $_SESSION['idCard'] = $createdCardRegister->Id;
        } catch (Exception $e) {
            $createdCardRegister = null;
            dump($e);
        }
        return $createdCardRegister;
    }

    public function updateCardRegistration($cardRegistration)
    {
        try {
            $cardInfo = $this->mangoPayApi->CardRegistrations->Update($cardRegistration);
        } catch (Exception $e) {
            $cardInfo = null;
            dump($e);
        }
        return $cardInfo;
    }

    public function createDirectPayin(User $user, Annonce $annonce)
    {
        $payIn = new \MangoPay\PayIn();
        $payIn->CreditedWalletId = $user->getidWallet();
        $payIn->AuthorId = $user->getIdMangopay();
        $payIn->DebitedFunds = new \MangoPay\Money();
        $payIn->DebitedFunds->Amount = $annonce->getPriceTotal();
        $payIn->DebitedFunds->Currency = 'EUR';
        $payIn->Fees = new \MangoPay\Money();
        $payIn->Fees->Amount = $annonce->getPriceTotal() - $annonce->getPriceOrigin();
        $payIn->Fees->Currency = 'EUR';
    }
}
