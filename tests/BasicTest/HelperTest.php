<?php

namespace Tests\BasicTest;

use Tests\TestCase;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Sender;

class HelperTest extends TestCase
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


    public function test_adds_prefix_if_not_exists_in_ensurePrefix()
    {
        $text = 'CustomProvider';

        // Test when the prefix is missing
        $result = Helper::ensurePrefix($text);
        $this->assertEquals('Xenon\\LaravelBDSms\\Provider\\CustomProvider', $result);

        // Test when the prefix already exists
        $textWithPrefix = 'Xenon\\LaravelBDSms\\Provider\\CustomProvider';
        $resultWithPrefix = Helper::ensurePrefix($textWithPrefix);
        $this->assertEquals('Xenon\\LaravelBDSms\\Provider\\CustomProvider', $resultWithPrefix);
    }


    public function test_validates_valid_mobile_numbers_in_numberValidation()
    {
        // Test valid mobile numbers
        $validNumbers = [
            '+8801234567890', // Valid with +88 prefix
            '8801234567890',  // Valid with 88 prefix
            '01234567890',    // Valid with 01 prefix
            '0123456789012',  // Valid with 01 prefix and 13 digits
        ];

        foreach ($validNumbers as $number) {
            $this->assertTrue(Helper::numberValidation($number));
        }
    }


    public function test_returns_false_for_invalid_mobile_numbers_in_numberValidation()
    {
        // Test invalid mobile numbers
        $invalidNumbers = [
            '1234567890',   // Invalid: no prefix
            '880123456789',  // Invalid: too short
            '01234',         // Invalid: too short
            'abcd1234',      // Invalid: non-numeric
        ];

        foreach ($invalidNumbers as $number) {
            $this->assertFalse(Helper::numberValidation($number));
        }
    }


    public function test_returns_comma_separated_numbers_in_getCommaSeperatedNumbers()
    {
        $numbers = ['01234567890', '01234567891', '01234567892'];

        // Test converting array to comma-separated string
        $result = Helper::getCommaSeperatedNumbers($numbers);
        $this->assertEquals('01234567890,01234567891,01234567892', $result);
    }


    public function test_checks_and_adds_prefix_in_checkMobileNumberPrefixExistence()
    {
        // Test with valid prefix
        $validMobile = '8801234567890';
        $resultValid = Helper::checkMobileNumberPrefixExistence($validMobile);
        $this->assertEquals('8801234567890', $resultValid);

        // Test with missing prefix
        $mobileWithoutPrefix = '1234567890';
        $resultMissingPrefix = Helper::checkMobileNumberPrefixExistence($mobileWithoutPrefix);
        $this->assertEquals('881234567890', $resultMissingPrefix);
    }


    public function test_ensures_number_starts_with_88_in_ensureNumberStartsWith88()
    {
        // Test when the number already starts with 88
        $validNumber = '8801234567890';
        $resultValid = Helper::ensureNumberStartsWith88($validNumber);
        $this->assertEquals('8801234567890', $resultValid);

        // Test when the number doesn't start with 88
        $numberWithoutPrefix = '1234567890';
        $resultWithoutPrefix = Helper::ensureNumberStartsWith88($numberWithoutPrefix);
        $this->assertEquals('881234567890', $resultWithoutPrefix);
    }
}
