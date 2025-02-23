<?php

namespace Tests\BasicTest;

use Tests\TestCase;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Provider\CustomGateway;
use Xenon\LaravelBDSms\Sender;

class SenderTest extends TestCase
{

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

}
