<?php

namespace Tests\BasicTest;

use Tests\TestCase;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Provider\CustomGateway;
use Xenon\LaravelBDSms\Sender;

class SenderTest extends TestCase
{

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Get the correct path to the src/Config/sms.php file from the package root
        $packageRootPath = dirname(__DIR__, 2); // Go two directories up from the test file
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

    public function test_can_get_instance_of_sender()
    {

        $instance = Sender::getInstance();
        $this->assertInstanceOf(Sender::class, $instance);
    }

    public function test_throws_exception_for_missing_config_file()
    {
        $this->expectException(RenderException::class);

        // Simulate missing config
        unlink(config_path('sms.php')); // Be cautious if testing locally!

        try {
            Sender::getInstance();
        } finally {
            // Restore the config file if it exists
            touch(config_path('sms.php'));
        }
    }

    /**
     * @throws RenderException
     */
    public function test_can_set_and_get_mobile()
    {
        $instance = Sender::getInstance();
        $instance->setMobile('1234567890');

        $this->assertEquals('1234567890', $instance->getMobile());
    }

    /**
     * @throws RenderException
     */
    public function test_can_set_and_get_message()
    {
        $instance = Sender::getInstance();
        $instance->setMessage('Hello, Laravel!');

        $this->assertEquals('Hello, Laravel!', $instance->getMessage());
    }


    public function test_can_set_a_valid_provider_class()
    {
        // Assuming CustomGateway extends AbstractProvider and is valid
        $sender = new Sender();
        $sender->setProvider(CustomGateway::class);

        $this->assertInstanceOf(Sender::class, $sender);
        $this->assertInstanceOf(CustomGateway::class, $sender->getProvider());
    }


    public function test_throws_exception_for_non_existent_provider_class()
    {
        $this->expectException(RenderException::class);

        $sender = new Sender();
        $sender->setProvider('NonExistentProvider');
    }


    public function test_throws_exception_for_provider_not_extending_abstract_provider()
    {
        // Creating a mock class that does not extend AbstractProvider
        $invalidProvider = new class {
            // Empty class that does not extend AbstractProvider
        };

        $this->expectException(RenderException::class);

        $sender = new Sender();
        $sender->setProvider(get_class($invalidProvider));
    }
}
