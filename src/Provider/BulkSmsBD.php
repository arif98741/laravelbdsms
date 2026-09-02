<?php
/*
 *  Last Modified: 6/29/21, 12:06 AM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
class BulkSmsBD extends AbstractProvider
{
    private string $apiEndpoint = 'https://bulksmsbd.net/api/smsapi';

    /**
     * BulkSmsBD's 'SMS Submitted Successfully' code.
     */
    private const SMS_SUBMITTED = 202;

    /**
     * Send Request To Api and Send Message
     * @throws GuzzleException|RenderException
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $query = [
            'api_key' => $config['api_key'],
            'senderid' => $config['senderid'],
            'type' => 'text',
            'number' => $number,
            'message' => $text,
        ];

        if (array_key_exists('senderid', $config)) {
            $query ['senderid'] = $config['senderid'];
        }

        if (is_array($number)) {
            $query['number'] =  implode(',', $number);
        }

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        return $this->respond($requestObject->get());
    }

    /**
     * BulkSmsBD answers `{"response_code":202,"success_message":"","error_message":""}`
     * on acceptance and the same shape with a 1xxx code on rejection, so its verdict
     * can be read without guessing.
     *
     * Only 202 is documented as success and only 1xxx as errors; anything else
     * returns null rather than false. An unfamiliar code is far more likely to be a
     * success this list has not seen than a silent failure, and reporting a message
     * that did go out as failed is the worse of the two mistakes.
     *
     * @param string $body
     * @return bool|null
     * @since v2.0.1.0-beta
     */
    public function accepted(string $body): ?bool
    {
        $payload = json_decode($body, true);

        if (!is_array($payload) || !isset($payload['response_code'])) {
            return null;
        }

        $code = (int)$payload['response_code'];

        if ($code === self::SMS_SUBMITTED) {
            return true;
        }

        //1001 invalid number, 1002 sender id not correct, 1007 balance insufficient,
        //1032 ip not whitelisted, and the rest of the documented family
        if ($code >= 1000 && $code < 1100) {
            return false;
        }

        return null;
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new ParameterException('api_key key is absent in configuration');
        }

        if (!array_key_exists('senderid', $this->senderObject->getConfig())) {
            throw new ParameterException('senderid key is absent in configuration');
        }



    }
}
