<?php

namespace Xenon\LaravelBDSms\Job;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Xenon\LaravelBDSms\Facades\Logger;

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
     * @return void
     * @throws JsonException
     */
    private function postMethodHandler()
    {
        $client = new Client([
            'base_uri' => $this->jobDetails['requestUrl'],
            'timeout' => $this->jobDetails['timeout'],
        ]);
        try {
            $response = $client->request('post', '', $this->jobDetails);
            $body = $response->getBody();
            $smsResult = $body->getContents();
            $log = [
                'provider' => $this->jobDetails['requestUrl'],
                'request_json' => json_encode($this->jobDetails['query'], JSON_THROW_ON_ERROR),
                'response_json' => json_encode($smsResult, JSON_THROW_ON_ERROR)
            ];
        } catch (GuzzleException|JsonException $e) {

            $log = [
                'provider' => $this->jobDetails['requestUrl'],
                'request_json' => json_encode($this->jobDetails['query'], JSON_THROW_ON_ERROR),
                'response_json' => json_encode($e->getMessage()),
            ];
        }
        $this->insertLoggerLog($log);
    }

    /**
     * @return void
     * @throws GuzzleException
     * @throws \JsonException
     */
    private function getMethodHandler()
    {
        $client = new Client([
            'base_uri' => $this->jobDetails['requestUrl'],
            'timeout' => $this->jobDetails['timeout'],
        ]);

        try {
            $response = $client->request('GET', '', $this->jobDetails);
            $body = $response->getBody();
            $smsResult = $body->getContents();
            $log = [
                'provider' => $this->jobDetails['requestUrl'],
                'request_json' => json_encode($this->jobDetails['query'], JSON_THROW_ON_ERROR),
                'response_json' => json_encode($smsResult, JSON_THROW_ON_ERROR)
            ];
        } catch (GuzzleException $e) {
            $log = [
                'provider' => $this->jobDetails['requestUrl'],
                'request_json' => json_encode($this->jobDetails['query'], JSON_THROW_ON_ERROR),
                'response_json' => json_encode($e->getMessage()),
            ];
        }

        $this->insertLoggerLog($log);
    }

    /**
     * @param array $log
     * @return void
     */
    private function insertLoggerLog(array $log): void
    {
        $config = Config::get('sms');
        if ($config['sms_log']) {
            Logger::createLog($log);
        }
    }
}
