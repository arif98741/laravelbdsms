<?php
/*
 *  Copyright (c) 2026
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;

class RhSmsBd extends AbstractProvider
{
    private string $apiEndpoint = 'https://rhsmsbd.top/api/v1/send';

    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     * @since v2.0.4.0
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        //the api documents a single `phone` field only, so a list is joined the
        //way the other bulk providers in this package do it
        $phone = is_array($number) ? implode(',', $number) : $number;

        $formParams = [
            'api_token' => $config['api_token'],
            'phone' => $phone,
            'message' => $text,
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint);
        $requestObject->setFormParams($formParams);

        //the joined phone is passed through so the report shows what was
        //actually sent, not the array that came in
        return $this->respond($requestObject->post(), $phone);
    }

    /**
     * RH SMS BD answers with a json body carrying a `status`. Its own api tester
     * treats exactly 'success' as sent and every other status as an error, and
     * this follows it.
     *
     * A body without a string `status` returns null rather than false: the
     * gateway publishes no response samples, and reporting a message that did go
     * out as failed is the worse of the two mistakes.
     *
     * @param string $body
     * @return bool|null
     * @since v2.0.4.0
     */
    public function accepted(string $body): ?bool
    {
        $payload = json_decode($body, true);

        if (!is_array($payload) || !isset($payload['status']) || !is_string($payload['status'])) {
            return null;
        }

        return strtolower(trim($payload['status'])) === 'success';
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('api_token', $this->senderObject->getConfig())) {
            throw new ParameterException('api_token is absent in configuration');
        }
    }
}
