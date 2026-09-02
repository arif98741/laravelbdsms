<?php
/*
 *  Last Modified: 6/28/21, 11:18 PM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;


use Illuminate\Http\JsonResponse;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

abstract class AbstractProvider implements ProviderRoadmap
{
    /**
     * @var
     */
    protected $senderObject;

    /**
     * Every provider is handed the sender it belongs to.
     *
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Build a request carrying the sender's queue settings, so a provider never
     * has to remember to forward them.
     *
     * @param string $url
     * @param array $query
     * @param array $headers
     * @return Request
     */
    protected function makeRequest(string $url, array $query = [], array $headers = []): Request
    {
        return new Request(
            $url,
            $query,
            $this->senderObject->getQueue(),
            $headers,
            $this->senderObject->getQueueName(),
            $this->senderObject->getTries(),
            $this->senderObject->getBackoff()
        );
    }

    /**
     * Turn a gateway response into this package's report. A queued send has no
     * response to read yet, so it just reports that the job was accepted.
     *
     * @param $response
     * @param null $number number to put in the report, when the provider reshaped it
     * @param null $message message to put in the report, when the provider reshaped it
     * @return bool|string
     */
    protected function respond($response, $number = null, $message = null)
    {
        if ($this->senderObject->getQueue()) {
            return true;
        }

        //read once: the body is a stream, and a second getContents() returns ''
        $body = $response->getBody()->getContents();

        //recorded rather than returned, so the report this method hands back is
        //byte for byte what it has always been
        $this->senderObject->setAcceptance($this->accepted($body));

        return $this->generateReport($body, [
            'number' => $number ?? $this->senderObject->getMobile(),
            'message' => $message ?? $this->senderObject->getMessage(),
        ])->getContent();
    }

    /**
     * Whether the gateway accepted the message, read from its own response body.
     *
     * The report from send() only tells a caller that the request completed; a
     * gateway answering 'sender id not registered' looks exactly like a delivered
     * message. Callers were left to parse each provider's body themselves, so a
     * rejection would silently pass for success — and an OTP job would mark itself
     * done having delivered nothing.
     *
     * Null means 'cannot tell', which is the default and still the case for most
     * providers: overriding this is opt-in per provider. Null must be treated as
     * 'no verdict', never as failure — reporting a send that did go out as failed
     * would be the worse error of the two.
     *
     * @param string $body raw response body as the gateway returned it
     * @return bool|null true accepted, false rejected, null no verdict available
     */
    public function accepted(string $body): ?bool
    {
        return null;
    }

    public function getData()
    {
        // TODO: Implement setData() method.

    }

    public function setData()
    {
        // TODO: Implement setData() method.
    }

    abstract public function sendRequest();

    /**
     * @param $result
     * @param $data
     * @return JsonResponse
     * @since v1.0.20
     * @version v1.0.20
     */
    public function generateReport($result, $data): JsonResponse
    {
        return response()->json([
            'status' => 'response',
            'response' => $result,
            'provider' => get_class($this),
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ]);
    }

    /**
     * @return mixed
     */
    abstract public function errorException();

    /**
     * Return Report As Array
     */
    public function toArray(): array
    {
        return [

        ];
    }

    /**
     * Return Report As Json
     * @throws \JsonException
     * @deprecated
     */
    public function toJson($data)
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
