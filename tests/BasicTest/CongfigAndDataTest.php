<?php

namespace Tests\BasicTest;

use Tests\TestCase;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Sender;

class CongfigAndDataTest extends TestCase
{

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Get the correct path to the src/Config/sms.php file from the package root
        //$packageRootPath = dirname(__DIR__, 2); // Go two directories up from the test file
        $packageRootPath = realpath(dirname(__DIR__, 2)); // Get the absolute path of the root directory by going two directories up
        //dd($packageRootPath);;

        $srcConfigPath = $packageRootPath . '/src/Config/sms.php';
        $destConfigPath = $packageRootPath . '/config/sms.php';

        // Ensure the source config file exists
        if (file_exists($srcConfigPath)) {
            // Create config directory if it doesn't exist
            if (!is_dir('config')) {
                mkdir('config', 0755, true);
            }
            copy($srcConfigPath, $destConfigPath);
        } else {
            throw new \Exception('Source config file src/Config/sms.php not found.');
        }
    }


    public function test_throws_exception_if_config_is_not_array()
    {
        $sender = new Sender();

        // Simulate a non-array config scenario
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage('config must be an array');

        $sender->setConfig('invalidConfig'); // Pass a non-array config
        $sender->send();
    }


    /**
     * @throws RenderException
     */
    public function test_throws_exception_if_mobile_is_empty()
    {
        $sender = new Sender();

        // Simulate missing mobile number
        $sender->setConfig(['key' => 'value']);
        $sender->setMessage('Test message');
        $sender->setProvider(Ssl::class);

        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage('Mobile number should not be empty');

        $sender->send();
    }


    /**
     * @throws RenderException
     * @throws \Exception
     */
    public function test_throws_exception_if_message_is_empty()
    {
        $sender = new Sender();

        // Simulate missing message
        $sender->setConfig(['key' => 'value']);
        $sender->setMobile('1234567890');
        $sender->setProvider(Ssl::class);

        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage('Message text should not be empty');

        $sender->send();
    }


    /**
     * @throws RenderException
     * @throws \Exception
     */
    public function test_throws_exception_if_mobile_and_message_are_empty()
    {
        $sender = new Sender();

        // Simulate both missing mobile number and message
        $sender->setConfig(['key' => 'value']);
        $sender->setProvider(Ssl::class);

        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage('Mobile number should not be empty');

        $sender->send();
    }


    /**
     * @throws RenderException
     * @throws ParameterException
     */
    public function test_can_send_sms_with_valid_config_data()
    {
        $sender = new Sender();

        // Simulate both missing mobile number and message
        $sender->setConfig(['key' => 'value']);
        $sender->setProvider(Ssl::class);
        $sender->setMobile('017XXYYZZAA');
        $sender->setMessage('text');

        $this->expectException(RenderException::class);
        $this->expectExceptionMessage('api_token key is absent in configuration');

        $sender->send();
    }
}
