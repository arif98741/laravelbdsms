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

class Khudebarta extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.sms.to/sms/send';

    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     * @since v2.0.2.0
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $recipient = $this->internationalFormat($number);

        $query = [
            'message' => $text,
            'to' => $recipient,
            'sender_id' => $config['sender_id'],
            'bypass_optout' => (bool)($config['bypass_optout'] ?? true),
        ];

        //sent only when configured: the gateway rejects an empty callback_url
        //rather than treating it as absent
        if (!empty($config['callback_url'])) {
            $query['callback_url'] = $config['callback_url'];
        }

        $headers = [
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Content-Type' => 'application/json',
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        $requestObject->setHeaders($headers)->setContentTypeJson(true);

        //the reshaped number is passed through so the report shows the number
        //that was actually sent, not the one that came in
        return $this->respond($requestObject->post(), $recipient);
    }

    /**
     * Khudebarta answers with a JSON body carrying a boolean `success`.
     *
     * Anything without that key returns null rather than false — an unfamiliar
     * response shape is far more likely to be a success this method has not seen
     * than a silent failure, and reporting a message that did go out as failed is
     * the worse of the two mistakes.
     *
     * @param string $body
     * @return bool|null
     * @since v2.0.2.0
     */
    public function accepted(string $body): ?bool
    {
        $payload = json_decode($body, true);

        if (!is_array($payload) || !array_key_exists('success', $payload)) {
            return null;
        }

        return (bool)$payload['success'];
    }

    /**
     * The api expects an E.164 recipient. Numbers are accepted in the shapes a
     * Bangladeshi application usually holds them in — 017XXXXXXXX, 88017XXXXXXXX
     * or +88017XXXXXXXX — and normalised to the last of those.
     *
     * A number already carrying a different country code is left alone, so this
     * provider can still send outside Bangladesh.
     *
     * @param string $number
     * @return string
     */
    private function internationalFormat(string $number): string
    {
        $number = trim($number);

        if (str_starts_with($number, '+')) {
            return $number;
        }

        if (str_starts_with($number, '88')) {
            return '+' . $number;
        }

        return '+88' . $number;
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new ParameterException('api_key is absent in configuration');
        }

        if (!array_key_exists('sender_id', $this->senderObject->getConfig())) {
            throw new ParameterException('sender_id key is absent in configuration');
        }
    }
}
