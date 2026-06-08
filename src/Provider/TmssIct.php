<?php
/*
 *  TMSS ICT SMS Gateway Provider
 *  API Documentation: https://sms.tmssict.com/api_documentation
 */

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class TmssIct extends AbstractProvider
{
    private string $apiEndpoint = 'https://sms.tmssict.com/api/v001/sent_sms';

    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries = $this->senderObject->getTries();
        $backoff = $this->senderObject->getBackoff();

        $mobile = $this->normalizeMobile($number);

        $payload = [
            'request_type' => $config['request_type'] ?? 'SINGLE_SMS',
            'message_type' => $config['message_type'] ?? 'TEXT',
            'mobile' => $mobile,
            'message_body' => $text,
            'campaign_title' => $config['campaign_title'] ?? 'LaravelBDSms',
        ];

        if (array_key_exists('isPromotional', $config) && $config['isPromotional'] !== '' && $config['isPromotional'] !== null) {
            $payload['isPromotional'] = (int) $config['isPromotional'];
        }

        $headers = [
            'Accept' => 'application/json',
            'api_key' => $config['api_key'],
        ];

        $requestObject = new Request($this->apiEndpoint, $payload, $queue, $headers, $queueName, $tries, $backoff);
        $requestObject->setContentTypeJson(true);
        $response = $requestObject->post();

        if ($queue) {
            return true;
        }

        $body = $response->getBody();
        $smsResult = $body->getContents();

        $data['number'] = $number;
        $data['message'] = $text;
        return $this->generateReport($smsResult, $data)->getContent();
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        $configArray = $this->senderObject->getConfig();

        if (!array_key_exists('api_key', $configArray) || empty($configArray['api_key'])) {
            throw new ParameterException('api_key is absent in configuration');
        }

        $messageType = $configArray['message_type'] ?? 'TEXT';
        $allowedMessageTypes = ['TEXT', 'UNICODE'];
        if (!in_array($messageType, $allowedMessageTypes, true)) {
            throw new ParameterException('message_type is invalid. Allowed values: ' . implode(', ', $allowedMessageTypes));
        }

        if (strlen((string) $this->senderObject->getMessage()) > 1000) {
            throw new ParameterException('message_body exceeds 1000 character limit allowed by TMSS ICT');
        }
    }

    /**
     * TMSS ICT requires 11-digit BD numbers without the 88 prefix and no special characters.
     * Accepts a single number or an array/comma-separated list (max 1000 per request).
     */
    private function normalizeMobile($number): string
    {
        if (is_array($number)) {
            $numbers = $number;
        } else {
            $numbers = explode(',', (string) $number);
        }

        $cleaned = [];
        foreach ($numbers as $n) {
            $digits = preg_replace('/\D+/', '', (string) $n);
            if (str_starts_with($digits, '880')) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '88') && strlen($digits) === 13) {
                $digits = substr($digits, 2);
            }
            if ($digits !== '') {
                $cleaned[] = $digits;
            }
        }

        return implode(',', $cleaned);
    }
}
