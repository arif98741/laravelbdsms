<?php

namespace Xenon\LaravelBDSms\Job;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\Http\Message\ResponseInterface;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private array $jobDetails;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $jobDetails)
    {
        $this->jobDetails = $jobDetails;
    }

    /**
     * Execute the job.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function handle()
    {
        if ($this->jobDetails['method'] == 'post') {

            return $this->postMethodHandler();
        } else {
            return $this->getMethodHandler();
        }
    }

    /**
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function postMethodHandler(): ResponseInterface
    {
        $client = new Client();
        return $client->post($this->jobDetails['requestUrl'], [
            RequestOptions::JSON => $this->jobDetails['query'],
            'verify' => $this->jobDetails['verify'],
            'timeout' => $this->jobDetails['timeout'],
        ]);
    }

    /**
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function getMethodHandler(): ResponseInterface
    {
        $client = new Client([
            'base_uri' => $this->jobDetails['requestUrl'],
            'timeout' => $this->jobDetails['timeout'],
        ]);

        return $client->request('GET', '', [
            'query' => $this->jobDetails['query'],
            'verify' => $this->jobDetails['verify'],
        ]);
    }
}
