<?php

namespace App\Mail;

use GuzzleHttp\Client;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class SendGridApiTransport extends AbstractTransport
{
    protected $client;
    protected $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => 'https://api.sendgrid.com/v3/',
            'timeout' => 30,
        ]);
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = [
            'personalizations' => [
                [
                    'to' => array_map(function ($address) {
                        return ['email' => $address->getAddress()];
                    }, $email->getTo()),
                ],
            ],
            'from' => [
                'email' => $email->getFrom()[0]->getAddress(),
                'name' => $email->getFrom()[0]->getName(),
            ],
            'subject' => $email->getSubject(),
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $email->getHtmlBody() ?? $email->getTextBody(),
                ],
            ],
        ];

        $response = $this->client->post('mail/send', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        if ($response->getStatusCode() !== 202) {
            throw new \Exception('SendGrid API request failed: ' . $response->getBody());
        }
    }

    public function __toString(): string
    {
        return 'sendgrid+api';
    }
}
