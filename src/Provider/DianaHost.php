<?php


namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Client;
use Xenon\LaravelBDSms\Handler\XenonException;
use Xenon\LaravelBDSms\Sender;

class DianaHost extends AbstractProvider
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

        $client = new Client([
            'base_uri' => 'http://esms.dianahost.com/smsapi',
            'timeout' => 10.0,
        ]);


        $response = $client->request('GET', '', [
            'query' => [
                'api_key' => $config['api_key'],
                'type' => $config['type'],
                'senderid' => $config['senderid'],
                'contacts' => $number,
                'msg' => $text,
            ]
        ]);
        $body = $response->getBody();
        $smsResult = $body->getContents();
        $data['number'] = $number;
        $data['message'] = $text;

        return $this->generateReport($smsResult, $data);
    }

    /**
     * @throws XenonException
     */
    public function errorException()
    {
        if (!is_array($this->senderObject->getConfig())) {
            throw new XenonException('Configuration is not provided. Use setConfig() in method chain');
        }

        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new XenonException('api_key is absent in configuration');
        }
        if (!array_key_exists('type', $this->senderObject->getConfig())) {
            throw new XenonException('type key is absent in configuration');
        }
        if (!array_key_exists('senderid', $this->senderObject->getConfig())) {
            throw new XenonException('senderid key is absent in configuration');
        }

        if (strlen($this->senderObject->getMobile()) > 11 || strlen($this->senderObject->getMobile()) < 11) {
            throw new XenonException('Invalid mobile number. It should be 11 digit');
        }
        if (empty($this->senderObject->getMessage())) {
            throw new XenonException('Message should not be empty');
        }
    }

    /**
     * @param $result
     * @param $data
     * @return array
     */
    public function generateReport($result, $data)
    {
        return response()->json([
            'status' => 'response',
            'response' => $result,
            'provider' => self::class,
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ]);
       /* return [
            'status' => 'response',
            'response' => $result,
            'provider' => self::class,
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ];*/
    }
}
