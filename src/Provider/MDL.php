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
use Xenon\Sender;

class MDL extends AbstractProvider
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
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $url = "http://premium.mdlsms.com/smsapi";
        $data = [
            "api_key" => $config['api_key'],
            "type" => $config['type'],
            "contacts" => $number,
            "senderid" => $config['senderid'],
            "msg" => $text,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $smsResult = curl_exec($ch);
        curl_close($ch);
        return $this->generateReport($smsResult, $data);
    }

    /**
     * @throws XenonException
     */
    public function errorException()
    {
        if (!is_array($this->senderObject->getConfig())) {
            throw new RenderException('Configuration is not provided. Use setConfig() in method chain');
        }
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new RenderException('api_key is absent in configuration');
        }
        if (!array_key_exists('type', $this->senderObject->getConfig())) {
            throw new RenderException('type key is absent in configuration');
        }
        if (strlen($this->senderObject->getMobile()) > 11 || strlen($this->senderObject->getMobile()) < 11) {
            throw new RenderException('Invalid mobile number. It should be 11 digit');
        }
        if (empty($this->senderObject->getMessage())) {
            throw new RenderException('Message should not be empty');
        }
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
