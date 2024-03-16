<?php

namespace Xenon\LaravelBDSms\Tests;

use ArgumentCountError;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;

class RequestTest extends TestCase
{

    public function test_check_passed_all_arguments()
    {
        $this->expectException(ArgumentCountError::class);
        new Request('http://example.com'); //at least 2 expected , we are passing 1
    }


    public function test_check_get_request()
    {
        $url = 'https://smsplus.sslwireless.com/api/v3/send-sms';
        $request = new Request($url,[]);
        $response = $request->get();
        $contents = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"status":"FAILED","status_code":4022,"error_message":"The api token field is required."}', $contents);
    }

    public function test_except_guzzle_exception()
    {
        $url = 'https://smsplus.sslwireless.co/api/v3/send-sfms'; //it's wrong url. should print exception
        $request = new Request($url,[]);
        $this->expectException(RenderException::class);
        $request->get();
    }

    public function test_form_params_is_passed()
    {
        $url = 'https://smsplus.sslwireless.com/api/v3/send-sms';
        $request = new Request($url,[]);
        $this->expectException(ArgumentCountError::class);
        $request->setFormParams();
    }
}
