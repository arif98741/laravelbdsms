<?php
/*
 *  Last Modified: 6/28/21, 11:18 PM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;


use Xenon\Handler\XenonException;
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Sender;


class Ssl extends AbstractProvider
{
    /**
     * BulkSmsBD constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     */
    public function sendRequest(): array
    {
        $mobile = $this->senderObject->getMobile();
        $config = $this->senderObject->getConfig();

        $apiToken = $config['api_token'];
        $sid = $config['sid'];
        $csms_id = $config['csms_id'];

        $data = [
            'number' => $mobile,
            'message' => $this->senderObject->getMessage()
        ];

        $message = $this->senderObject->getMessage();
        $url = "https://smsplus.sslwireless.com/api/v3/send-sms?api_token=$apiToken&sid=$sid&sms=$message&msisdn=$mobile&csms_id=$csms_id";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsResult = curl_exec($ch);
        if ($smsResult == false) {
            $smsResult = curl_error($ch);
        }
        curl_close($ch);
        return $this->generateReport($smsResult, $data);

    }

    /**
     * @throws XenonException
     */
    public function errorException()
    {
        if (!is_array($this->senderObject->getConfig()))
            throw new RenderException('Configuration is not provided. Use setConfig() in method chain');

        if (!array_key_exists('api_token', $this->senderObject->getConfig()))
            throw new RenderException('api_token key is absent in configuration');

        if (!array_key_exists('sid', $this->senderObject->getConfig()))
            throw new RenderException('sid key is absent in configuration');

        if (!array_key_exists('csms_id', $this->senderObject->getConfig()))
            throw new RenderException('csms_id key is absent in configuration');

        if (strlen($this->senderObject->getMobile()) > 11 || strlen($this->senderObject->getMobile()) < 11)
            throw new RenderException('Invalid mobile number. It should be 11 digit');

        if (Helper::numberValidation($this->senderObject->getMobile()) == false) {
            throw new RenderException('Invalid Mobile Number');
        }
        if (empty($this->senderObject->getMessage()))
            throw new RenderException('Message should not be empty');

    }

    /**
     * @param $result
     * @param $data
     * @return array
     */
    public function generateReport($result, $data): array
    {
        return [
            'status' => 'response',
            'response' => $result,
            'provider' => self::class,
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ];
    }
}
