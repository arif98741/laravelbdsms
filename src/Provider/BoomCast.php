<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Xenon\LaravelBDSms\Facades\Request;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class BoomCast extends AbstractProvider
{
    /**
     * BoomCast Constructor
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * @return JsonResponse
     * @throws GuzzleException
     * @throws RenderException
     * @version v1.0.37
     * @since v1.0.31
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $query = [
            "masking" => $config['masking'],
            "userName" => $config['username'],
            "password" => $config['password'],
            "MsgType" => "TEXT",
            "receiver" => $number,
            "message" => $text,
        ];

        $response = Request::get('https://api.boom-cast.com/boomcast/WebFramework/boomCastWebService/OTPMessage.php', $query, false);
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


        if (!array_key_exists('masking', $config))
            throw new RenderException('masking key is absent in configuration');

        if (!array_key_exists('username', $config))
            throw new RenderException('username key is absent in configuration');

        if (!array_key_exists('password', $config))
            throw new RenderException('password key is absent in configuration');
    }
}
