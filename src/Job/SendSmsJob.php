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
use Illuminate\Support\Facades\Log as LaravelLog;
use JsonException;
use Xenon\LaravelBDSms\Facades\Logger;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private array $jobDetails;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoffSeconds = 60;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $jobDetails)
    {
        $this->jobDetails = $jobDetails;
        if (isset($jobDetails['tries']) && is_integer($jobDetails['tries'])) {
            $this->tries = $jobDetails['tries'];
        }
        if (isset($jobDetails['backoff']) && is_integer($jobDetails['backoff'])) {
            $this->backoffSeconds = $jobDetails['backoff'];
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException|JsonException
     */
    public function handle()
    {

        if ($this->jobDetails['method'] == 'post') {
            $this->postMethodHandler();
        } else {
            $this->getMethodHandler();
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
                'response_json' => json_encode($e->getMessage(), JSON_THROW_ON_ERROR),
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

            if (array_key_exists('log_driver', $config)) {

                if ($config['log_driver'] === 'database') {
                    Logger::createLog($log);
                } else if ($config['log_driver'] === 'file') {
                    LaravelLog::info('laravelbdsms', $log);
                }
            } else {
                Logger::createLog($log);
            }
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): int
    {
        return $this->backoffSeconds;
    }
}
