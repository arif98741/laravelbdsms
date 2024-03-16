<?php

namespace Xenon\LaravelBDSms\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Sender;


class SendSmsTest extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @throws RenderException
     * @throws ParameterException
     * @throws \JsonException
     * @throws \Exception
     */
    public function test_check_ssl_sms_api_endpoint_is_ok_and_getting_correct_response()
    {
        $sender = Sender::getInstance();
        $sender->setProvider(Ssl::class);
        $sender->setMobile('017XXYYZZAA');
        $sender->setMessage('This is test message');
        $sender->setQueue(false);
        $sender->setConfig(
            [
                'api_token' => 'api_key_goes_here',
                'sid' => 'text',
                'csms_id' => 'approved_send_id',
            ]
        );

        $status = $sender->send();
        $jsonData = json_decode($status);
        $decodedResponse = $jsonData->response;
        $this->assertEquals('{"status":"FAILED","status_code":4001,"error_message":"Unauthorized"}', $decodedResponse);
    }

}
