<?php

namespace App\Service;

use App\Service\MockStorage;
use MangoPay;

class ApiPayOut
{
    private MangoPay\MangoPayApi $mangoPayApi;

    public function __construct()
    {
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $this->mangoPayApi->OAuthTokenManager->RegisterCustomStorageStrategy(new MockStorage());
    }

    public function Transfer($BuyerId, $SellerId, $BuyerWallet, $SellerWallet, $amount)
    {
        $Transfer = new \MangoPay\Transfer();
        $Transfer->AuthorId = $BuyerId;
        $Transfer->CreditedUserId = $SellerId;
        $Transfer->DebitedFunds = new \MangoPay\Money();
        $Transfer->DebitedFunds->Currency = "EUR";
        $Transfer->DebitedFunds->Amount = $amount * 100;
        $Transfer->Fees = new \MangoPay\Money();
        $Transfer->Fees->Currency = "EUR";
        $Transfer->Fees->Amount = 00;
        $Transfer->DebitedWalletId = $BuyerWallet;
        $Transfer->CreditedWalletId = $SellerWallet;

        $Result = $this->mangoPayApi->Transfers->Create($Transfer);
        return $Result;
    }

    public function PayOut($SellerId, $SellerWallet, $SellerBank, $amount)
    {

        $PayOut = new \MangoPay\PayOut();
        $PayOut->AuthorId = $SellerId;
        $PayOut->DebitedWalletId = $SellerWallet;
        $PayOut->DebitedFunds = new \MangoPay\Money();
        $PayOut->DebitedFunds->Currency = "EUR";
        $PayOut->DebitedFunds->Amount = $amount * 100;
        $PayOut->Fees = new \MangoPay\Money();
        $PayOut->Fees->Currency = "EUR";
        $PayOut->Fees->Amount = 00;
        $PayOut->PaymentType = \MangoPay\PayOutPaymentType::BankWire;
        $PayOut->MeanOfPaymentDetails = new \MangoPay\PayOutPaymentDetailsBankWire();
        $PayOut->MeanOfPaymentDetails->BankAccountId = $SellerBank;

        $result = $this->mangoPayApi->PayOuts->Create($PayOut);
        return $result;
    }
}