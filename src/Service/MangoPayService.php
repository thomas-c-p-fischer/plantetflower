<?php

namespace App\Service;


use App\Entity\User;
use Exception;
use MangoPay;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\BrowserInfo;
use MangoPay\CardRegistration;
use MangoPay\Money;
use MangoPay\PayIn;
use MangoPay\PayInExecutionDetailsDirect;
use MangoPay\PayInPaymentDetailsCard;
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

    //Methode permettant de créer les paramètres requis pour la creation d'une carte bancaire lors du click sur achat de l'annonce.
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
            $_SESSION['currency'] = $cardRegistration->Currency;
        } catch (Exception $e) {
            $createdCardRegister = null;
            dump($e);
        }
        return $createdCardRegister;
    }
//Methode qui récupère les paramètres de la methode ci-dessus afin de finaliser la creation de la carte sécurisée.
//Cette methode utilise la récupération d'un token unique via un controller de callBack (PaiementController) qui se déclenche à partir de annonceController $returnUrl.
    public function updateCardRegistration($cardRegistration)
    {
        try {
            $cardInfo = $this->mangoPayApi->CardRegistrations->Update($cardRegistration);
            $_SESSION['cardIdFinale'] = $cardInfo;

        } catch (Exception $e) {
            $cardInfo = null;
            dump($e);
        }
        return $cardInfo;
    }
// Une fois la carte créée le payin se fait : l'utilisateur se fait débiter de la somme de l'annonce voit son wallet créditer de cette somme, si la carte est valide(date expiration,bonne carte...)
// Les méthodes createCardRegistration, updateCardRegistration et createPayin se font les unes à la suite de l'autre et sont instanées si aucune d'elle ne rencontre d'erreur(s).
    public function createPayin(User $user, $cardId, $prixAnnonce, $fees, $id)
    {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $idMangoPayUser = $user->getIdMangopay();
        $idWalletUser = $user->getidWallet();
        try {
            $payIn = new PayIn();
            $payIn->AuthorId = $idMangoPayUser;
            $payIn->CreditedWalletId = $idWalletUser;
            $payIn->PaymentType = 'CARD';
            $payIn->ExecutionType = 'DIRECT';
            $payIn->DebitedFunds = new Money();
            $payIn->DebitedFunds->Currency = 'EUR';
            $payIn->DebitedFunds->Amount = $prixAnnonce * 100;
            $payIn->Fees = new Money();
            $payIn->Fees->Amount = $fees * 100;
            $payIn->Fees->Currency = 'EUR';
            $payIn->ExecutionDetails = new PayInExecutionDetailsDirect();
            $payIn->ExecutionDetails->SecureModeNeeded = true;
            $payIn->ExecutionDetails->SecureMode = 'FORCE';
            $payIn->ExecutionDetails->SecureModeReturnURL = "http://127.0.0.1:8000/annonce/" . $id . "/redirection";
            $payIn->PaymentDetails = new PayInPaymentDetailsCard();
            $payIn->PaymentDetails->CardId = $cardId;
            $payIn->PaymentDetails->IpAddress = $ip;
            $payIn->PaymentDetails->BrowserInfo = new BrowserInfo();
            $payIn->PaymentDetails->BrowserInfo->AcceptHeader = $_SERVER['HTTP_ACCEPT'];
            $payIn->PaymentDetails->BrowserInfo->JavaEnabled = false;
            $payIn->PaymentDetails->BrowserInfo->Language = 'FR';
            $payIn->PaymentDetails->BrowserInfo->ColorDepth = 24;
            $payIn->PaymentDetails->BrowserInfo->ScreenHeight = 1080;
            $payIn->PaymentDetails->BrowserInfo->ScreenWidth = 1920;
            $payIn->PaymentDetails->BrowserInfo->TimeZoneOffset = -120;
            $payIn->PaymentDetails->BrowserInfo->UserAgent = $_SERVER['HTTP_USER_AGENT'];
            $payIn->PaymentDetails->BrowserInfo->JavascriptEnabled = true;
            $result = $this->mangoPayApi->PayIns->Create($payIn);

            if ($result->Status == "FAILED") {
                dump("failed");
            } elseif ($result->ExecutionDetails->SecureModeNeeded) {
                header("Location: " . $result->ExecutionDetails->SecureModeRedirectURL);
                die();
            } elseif ($result->Status == "SUCCEEDED") {
                dump('ok');
            } else {
                dump('something weird happened');
            }
            return $result;
        } catch (MangoPay\Libraries\Exception $e) {
            dump($e);
        }

    }
    //Une fois le payIn exécuté on transfère l'argent du wallet de l'acheteur
    // vers celui du vendeur en créant un Transfer
    public function createTransfer(User $user, $prixAnnonce, $sellerWalletId)
    {
        $buyerId = $user->getIdMangopay();
        $buyerWalletId = $user->getidWallet();
        try {
            $transfer = new \MangoPay\Transfer();
            $transfer->AuthorId = $buyerId;
            $transfer->CreditedUserId = $buyerId;
            $transfer->DebitedFunds = new \MangoPay\Money();
            $transfer->DebitedFunds->Currency = "EUR";
            $transfer->DebitedFunds->Amount = $prixAnnonce * 100;
            $transfer->Fees = new \MangoPay\Money();
            $transfer->Fees->Currency = "EUR";
            $transfer->Fees->Amount = 0;
            $transfer->DebitedWalletId = $buyerWalletId;
            $transfer->CreditedWalletId = $sellerWalletId;
            $result = $this->mangoPayApi->Transfers->Create($transfer);
        } catch (MangoPay\Libraries\Exception $e) {
            dump($e);
        }
        return $result;
    }

    //Dans cette méthode, on récupère l'id du compte bancaire du vendeur
    public function getBankAccountId($sellerId)
    {
        try {
            $bankAccount = $this->mangoPayApi->Users->GetBankAccounts($sellerId);
            return $bankAccount[0]->Id;
        } catch (MangoPay\Libraries\Exception $e) {
            dump($e);
        }

    }
    //Après avoir récupéré l'id du compte bancaire du vendeur, on lui transfère l'argent depuis son wallet vers
    //son compte bancaire en virant l'argent dessus.
    public function createPayOut($sellerWalletId, $idBankAccount, $sellerId, $prixAnnonce)
    {
        try {
            $payOut = new MangoPay\PayOut();
            $payOut->AuthorId = $sellerId;
            $payOut->DebitedWalletId = $sellerWalletId;
            $payOut->DebitedFunds = new Money();
            $payOut->DebitedFunds->Currency = "EUR";
            $payOut->DebitedFunds->Amount = $prixAnnonce * 100;
            $payOut->Fees = new Money();
            $payOut->Fees->Currency = "EUR";
            $payOut->Fees->Amount = 0;
            $payOut->PaymentType = "BANK_WIRE";
            $payOut->MeanOfPaymentDetails = new MangoPay\PayOutPaymentDetailsBankWire();
            $payOut->MeanOfPaymentDetails->BankAccountId = $idBankAccount;
            $payOut->MeanOfPaymentDetails->ModeRequested = 'INSTANT_PAYMENT';
            return $this->mangoPayApi->PayOuts->Create($payOut);
        } catch (MangoPay\Libraries\Exception $e) {
            dump($e);
        }
    }
}
