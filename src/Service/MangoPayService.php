<?php

namespace App\Service;

use App\Entity\User;
use MangoPay;

class MangoPayService
{
    private MangoPay\MangoPayApi $mangoPayApi;


    public function __construct()
    {
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $this->mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
        $this->mangoPayApi->Config->TemporaryFolder = 'D:\Thomas\Dév\PhpstormProjects\plantetflower\public';

    }


    public function createNaturalUser($firstName, $lastName, $email)
    {
        $newUser = new \MangoPay\UserNatural();
        $newUser->Email = $email;
        $newUser->FirstName = $firstName;
        $newUser->LastName = $lastName;
        $newUser->Birthday = 121271;
        $newUser->Nationality = "GB";
        $newUser->CountryOfResidence = "FR";
        $result = $this->mangoPayApi->Users->Create($newUser);
        return $result->Id;
    }

}