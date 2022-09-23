<?php

namespace Xenon\LaravelBDSms;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Job\SendSmsJob;

class Request extends Controller
{

    private bool $queue;

    private string $requestUrl;

    private array $query;

    /**
     * Constructor For Initiating Value
     * @param $requestUrl
     * @param array $query
     * @param bool $queue
     */
    public function __construct($requestUrl, array $query, bool $queue = false)
    {
        $this->requestUrl = $requestUrl;
        $this->query = $query;
        $this->queue = $queue;
    }


    /**
     * @param false $verify
     * @throws GuzzleException
     * @throws RenderException
     */
    public function get(bool $verify = false, $timeout = 10.0)
    {
        if ($this->getQueue()) {

            dispatch(new SendSmsJob([
                'requestUrl' => $this->requestUrl,
                'query' => $this->query,
                'verify' => $verify,
                'timeout' => $timeout,
                'method' => 'post',
            ]));
        } else {

            $client = new Client([
                'base_uri' => $this->requestUrl,
                'timeout' => $timeout,
            ]);

            try {
                return $client->request('GET', '', [
                    'query' => $this->query,
                    'verify' => $verify
                ]);
            } catch (GuzzleException|ClientException $e) {
                throw new RenderException($e->getMessage());
            }
        }
    }

    /**
     * @param false $verify
     * @throws RenderException
     */
    public function post(bool $verify = false, $timeout = 10.0)
    {

        try {
            if ($this->getQueue()) {

                dispatch(new SendSmsJob([
                    'requestUrl' => $this->requestUrl,
                    'query' => $this->query,
                    'verify' => $verify,
                    'timeout' => $timeout,
                    'method' => 'post',
                ]));
            } else {
                $client = new Client();
                return $client->post($this->requestUrl, [
                    RequestOptions::JSON => $this->query,
                    'verify' => $verify,
                    'timeout' => $timeout,
                ]);
            }

        } catch (GuzzleException|ClientException $e) {
            throw new RenderException($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function getQueue(): bool
    {
        return $this->queue;
    }

    /**
     * @param bool $queue
     * @return void
     */
    public function setQueue(bool $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return mixed
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     * @param mixed $requestUrl
     */
    public function setRequestUrl($requestUrl): void
    {
        $this->requestUrl = $requestUrl;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param mixed $query
     */
    public function setQuery($query): void
    {
        $this->query = $query;
    }


}
