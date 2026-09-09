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

class ZendSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.zendsms.com/api/v1/send-sms';

    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     * @since v2.0.3.0
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        //the api takes a single `recipient` field, so a list is joined the way the
        //other bulk providers in this package do it
        $recipient = is_array($number) ? implode(',', $number) : $number;

        $query = [
            'recipient' => $recipient,
            'sender_id' => $config['sender_id'],
            'message' => $text,
        ];

        //sent only when configured: client_ref is the gateway's own correlation
        //field and an empty one is worse than none
        if (!empty($config['client_ref'])) {
            $query['client_ref'] = $config['client_ref'];
        }

        $headers = [
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Content-Type' => 'application/json',
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        $requestObject->setHeaders($headers)->setContentTypeJson(true);

        //the joined recipient is passed through so the report shows what was
        //actually sent, not the array that came in
        return $this->respond($requestObject->post(), $recipient);
    }

    /**
     * ZendSms answers with a json envelope. The shapes recognised here are the
     * explicit ones: a boolean `success`, or a `status` naming the outcome.
     *
     * Anything else returns null rather than false. This gateway's body has not
     * been confirmed against a live account yet, and reporting a message that did
     * go out as failed is the worse of the two mistakes — so an unfamiliar shape
     * is treated as 'cannot tell', never as a rejection.
     *
     * @param string $body
     * @return bool|null
     * @since v2.0.3.0
     */
    public function accepted(string $body): ?bool
    {
        $payload = json_decode($body, true);

        if (!is_array($payload)) {
            return null;
        }

        if (array_key_exists('success', $payload) && is_bool($payload['success'])) {
            return $payload['success'];
        }

        if (!isset($payload['status']) || !is_string($payload['status'])) {
            return null;
        }

        $status = strtolower(trim($payload['status']));

        if (in_array($status, ['success', 'sent', 'queued', 'accepted', 'ok'], true)) {
            return true;
        }

        if (in_array($status, ['error', 'failed', 'fail', 'rejected'], true)) {
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
            throw new ParameterException('api_key is absent in configuration');
        }

        if (!array_key_exists('sender_id', $this->senderObject->getConfig())) {
            throw new ParameterException('sender_id key is absent in configuration');
        }
    }
}
