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

use Illuminate\Support\Str;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;

/**
 * GotiSMS (https://gotisms.com/docs/sms-api)
 *
 * Sends through `POST /api/messages/send`, the immediate-send endpoint. It
 * accepts only the `transactional` and `otp` categories; promotional traffic
 * has to go through the gateway's campaign approval flow and is refused here
 * with a 409.
 */
class GotiSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.gotisms.com/api/messages/send';

    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     * @since v2.0.6.0
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        //the api takes a json array; a comma separated string is split so the
        //`shoot('017..,018..')` form the other providers accept works here too
        $recipients = is_array($number) ? $number : explode(',', (string)$number);
        $recipients = array_values(array_filter(array_map('trim', $recipients), 'strlen'));

        $query = [
            'recipients' => $recipients,
            'senderId' => $config['senderId'],
            'message' => $text,
            'category' => $config['category'] ?? 'transactional',
        ];

        if (!empty($config['title'])) {
            $query['title'] = $config['title'];
        }

        $headers = [
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            //a fresh key per send, fixed into the request options: a queued job
            //that is retried after a timeout replays the same key, so the gateway
            //returns the first result instead of sending and charging twice
            'Idempotency-Key' => (string)Str::uuid(),
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        $requestObject->setHeaders($headers)->setContentTypeJson(true);

        return $this->respond($requestObject->post(), implode(',', $recipients));
    }

    /**
     * GotiSMS answers a 2xx send with `accepted` and `rejected` counts plus a
     * per-recipient `results` list. Errors (400 validation, 401 key, 403, 409,
     * 429) come back as non-2xx and are raised as a RenderException before
     * this is reached.
     *
     * Every recipient accepted is true, every one rejected is false. A partly
     * rejected bulk send, or a body without the counts, returns null: reporting
     * a message that did go out as failed is the worse of the two mistakes.
     *
     * @param string $body
     * @return bool|null
     * @since v2.0.6.0
     */
    public function accepted(string $body): ?bool
    {
        $payload = json_decode($body, true);

        if (!is_array($payload)
            || !isset($payload['accepted'], $payload['rejected'])
            || !is_numeric($payload['accepted'])
            || !is_numeric($payload['rejected'])) {
            return null;
        }

        $accepted = (int)$payload['accepted'];
        $rejected = (int)$payload['rejected'];

        if ($accepted > 0 && $rejected === 0) {
            return true;
        }

        if ($accepted === 0 && $rejected > 0) {
            return false;
        }

        return null;
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (empty($config['api_key'])) {
            throw new ParameterException('api_key is absent in configuration');
        }

        if (empty($config['senderId'])) {
            throw new ParameterException('senderId key is absent in configuration');
        }

        if (isset($config['category']) && !in_array($config['category'], ['transactional', 'otp'], true)) {
            throw new ParameterException('category must be transactional or otp; promotional sends need the GotiSMS campaign flow');
        }
    }
}
