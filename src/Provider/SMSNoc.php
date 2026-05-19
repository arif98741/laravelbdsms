<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class SMSNoc extends AbstractProvider
{
    /**
     * Official API Endpoint for SMS NOC
     * @var string
     */
    private string $apiEndpoint = 'https://smsnoc.com/api/v1/send-sms';

    /**
     * SMSNoc constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @return mixed
     * @throws RenderException
     */
    public function sendRequest()
    {
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        // Handle array of numbers if passed
        $numberStr = is_array($mobile) ? implode(',', $mobile) : $mobile;

        // Robust number normalization to +880 format
        $phone = preg_replace('/[^0-9+]/', '', $numberStr);
        if (!str_starts_with($phone, '+880') && !str_starts_with($phone, '880')) {
            $phone = str_starts_with($phone, '0') ? '+88' . $phone : '+880' . $phone;
        } elseif (str_starts_with($phone, '880')) {
            $phone = '+' . $phone;
        }

        $client = new Client([
            'timeout' => 20.0,
        ]);

        try {
            $response = $client->request('POST', $this->apiEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $config['bearer_token'],
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json' => [
                    'to'        => $phone,
                    'message'   => $text,
                    'sender_id' => $config['sender_id'],
                ],
            ]);

            $body = $response->getBody();
            $smsResult = $body->getContents();

            $data['number'] = $mobile;
            $data['message'] = $text;

            return $this->generateReport($smsResult, $data)->getContent();

        } catch (GuzzleException $e) {
            throw new RenderException($e->getMessage());
        }
    }

    /**
     * @throws RenderException
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('sender_id', $config)) {
            throw new RenderException('sender_id key is absent in configuration');
        }
        if (!array_key_exists('bearer_token', $config)) {
            throw new RenderException('bearer_token key is absent in configuration');
        }
    }
}
