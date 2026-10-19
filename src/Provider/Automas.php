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

class Automas extends AbstractProvider
{
    /**
     * The json endpoint is used rather than the plain http one (smsapiv3)
     * because it is the only one whose response body the gateway documents.
     */
    private string $apiEndpoint = 'https://api.automas.com.bd/smsapiv4';

    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     * @since v2.0.5.0
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        //the gateway's bulk example joins numbers in `contacts` with '+', not the
        //comma its plain http api uses
        $contacts = is_array($number) ? implode('+', $number) : $number;

        $query = [
            'api_key' => $config['api_key'],
            'senderid' => $config['senderid'],
            'type' => $config['type'] ?? 'text',
            //an empty string is the gateway's own way of saying 'send now'
            'scheduledDateTime' => $config['scheduledDateTime'] ?? '',
            'msg' => $text,
            'contacts' => $contacts,
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query, [
            'Content-Type' => 'application/json',
        ]);
        $requestObject->setContentTypeJson(true);

        //the joined contacts are passed through so the report shows what was
        //actually sent, not the array that came in
        return $this->respond($requestObject->post(), $contacts);
    }

    /**
     * Automas answers with `[{"status": 0, "id": ..., "msisdn": ...}]` (its docs
     * show it wrapped in `{"response": ...}`, the live api does not), one item
     * per recipient, where status 0 is success and anything else is one
     * of its documented error codes (102 sender not valid, 1000 insufficient
     * balance, ...).
     *
     * Every item 0 is accepted and every item non-zero is rejected. A bulk send
     * where only some recipients failed returns null, as does any body of another
     * shape: the gateway documents no error body, and reporting a message that
     * did go out as failed is the worse of the two mistakes.
     *
     * @param string $body
     * @return bool|null
     * @since v2.0.5.0
     */
    public function accepted(string $body): ?bool
    {
        $payload = json_decode($body, true);

        //the live gateway answers with the bare list; the documented
        //`response` wrapper is read too in case it ever sends that
        //array_values() === itself stands in for array_is_list(), which is php 8.1+
        $isList = static fn($value) => is_array($value) && array_values($value) === $value;
        $items = $isList($payload) ? $payload : ($payload['response'] ?? null);

        if (!$isList($items) || $items === []) {
            return null;
        }

        $statuses = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['status']) || !is_numeric($item['status'])) {
                return null;
            }
            $statuses[] = (int)$item['status'] === 0;
        }

        if (!in_array(false, $statuses, true)) {
            return true;
        }

        if (!in_array(true, $statuses, true)) {
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

        if (!array_key_exists('senderid', $this->senderObject->getConfig())) {
            throw new ParameterException('senderid key is absent in configuration');
        }
    }
}
