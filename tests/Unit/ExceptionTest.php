<?php

namespace Xenon\LaravelBDSms\Tests;

use PHPUnit\Framework\TestCase;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Sender;

class ExceptionTest extends TestCase
{

    /**
     * @throws RenderException
     * @throws \JsonException
     */
    public function test_expect_exception_when_configuration_is_empty()
    {
        $sender = Sender::getInstance();
        $sender->setProvider(Ssl::class);
        $this->expectException(ParameterException::class);
        $sender->send();
    }

    /**
     * @throws RenderException
     * @throws \JsonException
     * @throws \Exception
     */
    public function test_expect_exception_when_mobile_is_empty()
    {
        $sender = Sender::getInstance();
        $sender->setProvider(Ssl::class);
        $sender->setConfig([

        ]);
        $this->expectException(ParameterException::class);
        $sender->send();
    }

    /**
     * @throws RenderException
     * @throws \JsonException
     * @throws \Exception
     */
    public function test_expect_exception_when_message_is_empty()
    {
        $sender = Sender::getInstance();
        $sender->setProvider(Ssl::class);
        $sender->setConfig([

        ]);
        $sender->setMobile('01xxxxxxxxxxx');
        $this->expectException(ParameterException::class);
        $sender->send();
    }

}
