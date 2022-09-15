<?php

namespace App\MailJet;

/*
This call sends a message based on a template.
*/

// require 'vendor/autoload.php';
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use \Mailjet\Resources;
use \Mailjet\Client;
use \Mailjet\Request;
use \Mailjet\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Mail extends AbstractController
{
    //envoi du mail de confirmation
    public function send($mailTo, $name, $template, $token, $subject, $title, $subtitle, $content)
    {

        $mailjet = new Client($_ENV['MAILJET_API_KEY'], $_ENV['MAILJET_API_KEY_PRIVATE'], true, ['version' => 'v3.1']);

        // $mj = new \Mailjet\Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'),true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "myflowerlifeantigaspi@gmail.com",
                        'Name' => "Plant & Flower"
                    ],
                    'To' => [
                        [
                            'Email' => $mailTo,
                            'Name' => $name
                        ]
                    ],
                    'TemplateID' => $template,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                        'title' => $title,
                        'subtitle' => $subtitle,
                        'token' => $token
                    ]

                ]
            ]
        ];
        $response = $mailjet->post(Resources::$Email, ['body' => $body]);
        $response->success() && var_dump($response->getData());
    }


    //envoi du mail de réinitialisation de mdp
    public function sendResetPassword($mailTo, $name, $template, $tokenPassword, $subject, $title, $subtitle, $content)
    {

        $mailjet = new Client($_ENV['MAILJET_API_KEY'], $_ENV['MAILJET_API_KEY_PRIVATE'], true, ['version' => 'v3.1']);

        // $mj = new \Mailjet\Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'),true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "myflowerlifeantigaspi@gmail.com",
                        'Name' => "Plant & Flower"
                    ],
                    'To' => [
                        [
                            'Email' => $mailTo,
                            'Name' => $name
                        ]
                    ],
                    'TemplateID' => $template,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                        'title' => $title,
                        'subtitle' => $subtitle,
                        'tokenPassword' => $tokenPassword
                    ]

                ]
            ]
        ];

        $response = $mailjet->post(Resources::$Email, ['body' => $body]);
        $response->success() && var_dump($response->getData());
    }

    public function contact($form)
    {
        $mailjet = new Client($_ENV['MAILJET_API_KEY'], $_ENV['MAILJET_API_KEY_PRIVATE'], true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $form['email'],
                        'Name' => $form['name']
                    ],
                    'To' => [
                        [
                            'Email' => "myflowerlifeantigaspi@gmail.com",
                            'Name' => "Plant & Flower"
                        ]
                    ],
                    'TemplateID' => "",
                    'TemplateLanguage' => true,
                    'Subject' => $form['subject'],
                    'Variables' => [
                        'content' => $form['message'],
                    ]

                ]
            ]
        ];
        $response = $mailjet->post(Resources::$Email, ['body' => $body]);
        $response->success() && var_dump($response->getData());
    }
}

