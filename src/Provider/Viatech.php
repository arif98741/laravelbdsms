<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Xenon\LaravelBDSms\Facades\Request;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class Viatech extends AbstractProvider
{
    /**
     * Viatech Constructor
     * @param Sender $sender
     * @version v1.0.38
     * @since v1.0.38
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * @return JsonResponse
     * @throws GuzzleException
     * @throws RenderException
     * @version v1.0.38
     * @since v1.0.38
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $query = [
            "api_key" => $config['api_key'],
            "mask" => $config['mask'],
            "recipient" => $number,
            "message" => $text,
        ];

        $response = Request::get('http://masking.viatech.com.bd/smsnet/bulk/api', $query, false);

        $body = $response->getBody();
        $smsResult = $body->getContents();

        $data['number'] = $number;
        $data['message'] = $text;
        $report = $this->generateReport($smsResult, $data);
        return $report->getContent();
    }

    /**
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();
        if (!array_key_exists('api_key', $config))
            throw new RenderException('api_key key is absent in configuration');

        if (!array_key_exists('mask', $config))
            throw new RenderException('mask key is absent in configuration');

    }
}
