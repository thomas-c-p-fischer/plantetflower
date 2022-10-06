<?php

namespace App\Service;

use App\Entity\User;
use MangoPay;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\CardRegistration;
use MangoPay\Wallet;

class MangoPayService
{
    private MangoPay\MangoPayApi $mangoPayApi;
    private $apiUsers;

    //Construteur qui sert a l'initialisation de l'api. les "$_ENV" sont les élèments a compléter dans le .env.local.
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

    //Methode permettant d'ajouter un KYC created.
    public function createKYCDocument(User $user, $document)
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

    //Cette methode est reliée à celle du dessus. Lorsque l'utilisateur insérer ses pieces justificative lors de la creation, la methode ci-dessous
    //Envoie ces derniers à mangoPay et passe sous le statut "VALIDATION ASKED". MangoPay se charge de les verifier et de les accepter s'ils sont valides.
    public function submitKYCDocument(User $user, $KYCDocId)
    {
        $userId = $user->getIdMangopay();
        $KYCDocument = new \MangoPay\KycDocument();
        $KYCDocument->Id = $KYCDocId;
        $KYCDocument->Status = \MangoPay\KycDocumentStatus::ValidationAsked; // VALIDATION_ASKED
        return $this->mangoPayApi->Users->UpdateKycDocument($userId, $KYCDocument);
    }

    //Methode de Thomas.
    public function payIn(User $user, $cardNumber, $expirationDate, $cvc)
    {
        $mangoPayApi = $this->mangoPayApi;
        $userId = $user->getIdMangopay();
        $card = new CardRegistration();
        $card->UserId = $userId;
        $card->Currency = "EUR";
        $card->CardType = "CB_VISA_MASTERCARD";
        $cardRegister = $this->mangoPayApi->CardRegistrations->Create($card);
        $updatedCardRegister = $mangoPayApi->CardRegistrations->Update($cardRegister);
        if ($updatedCardRegister->Status != MangoPay\CardRegistrationStatus::Validated
            || !isset($updatedCardRegister->CardId)) {
            die('<div style="color:red;">La carte n\'est pas reconnue. Le paiement n\'a pas eu lieu.<div>');
        }

        $cardProperties = $cardRegister->GetReadOnlyProperties();
        $validatedCard = $mangoPayApi->Cards->Get($updatedCardRegister->CardId);

    }

//Methode pour l'enregistrement de la carte.
    public function registrationCard(User $user)
    {
        $idMangoPayUser = $user->getIdMangopay();
        $card = new MangoPay\CardRegistration();
        $card->UserId = $idMangoPayUser;
        $card->CardType = "CB_VISA_MASTERCARD";
        $card->Currency = 'EUR';
        $card->Status = MangoPay\CardRegistrationStatus::Validated;
        if ($card->Status != \MangoPay\CardRegistrationStatus::Validated || !isset($card->CardId)) {
            die('<div style="color:red;">"Le paiement n\'a pas été accepté. Vérifier votre carte"<div>');
        } else {
            $cardCreated = $this->mangoPayApi->CardRegistrations->Create($card);
            $_SESSION['cardRegisterId'] = $cardCreated->Id;
        }
        return $cardCreated;

    }

    public function createPayInForUserByWallet(User $user)
    {
        $IdWalletUser = $user->getidWallet();
        $registrationCard = $this->mangoPayApi->CardRegistrations->Get($_SESSION['cardRegisterId']);
        $registrationCard->PreregistrationData = "data=" . $_GET["data"];
        $registrationCard->AccessKey = "acceskey=" . $_GET['accessKeyRef'];
        $registrationCard->CardRegistrationURL = $this->mangoPayApi->Config->BaseUrl;
    }
}
