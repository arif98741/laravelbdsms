<?php

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
class SMSNoc extends AbstractProvider
{
    /**
     * Official API Endpoint for SMS NOC
     * @var string
     */
    private string $apiEndpoint = 'https://smsnoc.com/api/v1/send-sms';


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

        $query = [
            'to' => $phone,
            'message' => $text,
            'sender_id' => $config['sender_id'],
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $config['bearer_token'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query, $headers);
        $requestObject->setContentTypeJson(true);

        return $this->respond($requestObject->post());
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
