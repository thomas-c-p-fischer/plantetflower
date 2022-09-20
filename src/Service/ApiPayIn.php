<?php

namespace App\Service;

use App\Service\MockStorage;
use App\Entity\User;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use MangoPay;

class ApiPayIn
{
    private MangoPay\MangoPayApi $mangoPayApi;
    private HttpClientInterface $client;
    private $PreFinal;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->client = $httpClient;

        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $this->mangoPayApi->OAuthTokenManager->RegisterCustomStorageStrategy(new MockStorage());
    }

    public function Payin($WalletId, $BuyerId, $annoncePoids, $shipment, $CardId, $OriginPrice, $ExistingCard): MangoPay\PayIn
    {
        $response = $this->client->request(
            'GET',
            'https://api.sandbox.mangopay.com/v2.01/' . $_ENV['CLIENT_ID'] . '/cards' . '/' . $CardId . '/',
            [
                'auth_basic' => [
                    $_ENV['CLIENT_ID'], // username
                    $_ENV['API_KEY']    //password
                ]
            ]
        );

        $card = $response->toArray();


        if ($shipment) {
            if ($annoncePoids === "0g - 500g") {
                $mondialFees = 4.4;
            } else if ($annoncePoids === "500g - 1kg") {
                $mondialFees = 4.9;
            } else if ($annoncePoids === "1kg - 2kg") {
                $mondialFees = 6.4;
            } else if ($annoncePoids === "2kg - 3kg") {
                $mondialFees = 6.6;
            }
        } else {
            $mondialFees = 0;
        }
        $mondialMangoPercent = $mondialFees * 0.12;
        $fixFees = 0.7;
        $percentPrice = $OriginPrice * 0.12;
        $TotalPrice = round($OriginPrice + $fixFees + $percentPrice + $mondialFees + $mondialMangoPercent, 3);


        $modPrice = fmod($TotalPrice, 1);
        //Pour obtenir le rest et déterminer l'arrondi correspondant avec les conditions ci dessous


        if ($modPrice > 0 && $modPrice < 0.5) {
            $PreFinal = round($TotalPrice, 0) + 0.5;
        } else if ($modPrice >= 0.5 && $modPrice < 1) {
            $PreFinal = round($TotalPrice, 0);
        }
        $reste = $PreFinal - $TotalPrice;
        // $commissionSite = $totalFees-(0.018 *$PreFinal +0.18);
        // $a = array($PreFinal, $TotalPrice,$totalFees, $commissionSite);

        $payIn = new \MangoPay\PayIn();
        $payIn->CreditedWalletId = $WalletId;
        $payIn->AuthorId = $BuyerId;
        $payIn->DebitedFunds = new \MangoPay\Money();
        $payIn->DebitedFunds->Amount = $PreFinal * 100;
        $payIn->DebitedFunds->Currency = 'EUR';
        $payIn->Fees = new \MangoPay\Money();
        $payIn->Fees->Amount = ($mondialMangoPercent + $fixFees + $percentPrice + $mondialFees + $reste) * 100;
        $payIn->Fees->Currency = 'EUR';
        $payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
        $payIn->PaymentDetails->CardType = $card['CardType'];
        $payIn->PaymentDetails->CardId = $card['Id'];
        $payIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsDirect();
        $payIn->ExecutionDetails->SecureModeReturnURL = 'http://test.com';

        return $this->mangoPayApi->PayIns->Create($payIn);
    }
}