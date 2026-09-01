<?php

namespace Xenon\LaravelBDSms\Log;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log as LaravelLog;
use Throwable;

/**
 * Posts the sms log to a discord channel through an incoming webhook, rendered
 * as an embed with a monospace table.
 *
 * Logging happens after the sms has already gone out, so a webhook that is
 * missing, rate limited or unreachable must never turn a delivered sms into an
 * exception. Every failure is reported to the laravel log instead.
 */
class DiscordLog
{
    /**
     * Discord payload limits.
     */
    private const DESCRIPTION_LIMIT = 4000;
    private const FIELD_LIMIT = 1000;

    /**
     * Left column width of the table.
     */
    private const LABEL_WIDTH = 11;

    /**
     * Discord renders plain newlines, and PHP_EOL would make the payload size
     * platform dependent (two bytes on windows, one elsewhere).
     */
    private const LINE_BREAK = "\n";

    /**
     * Embed colours.
     */
    private const COLOUR_SUCCESS = 0x2ECC71;
    private const COLOUR_FAILURE = 0xE74C3C;
    private const COLOUR_NEUTRAL = 0x5865F2;

    /**
     * The missing webhook notice is worth saying once, not once per sms.
     *
     * @var bool
     */
    private static bool $webhookWarned = false;

    /**
     * @param array $data
     * @return void
     */
    public function createLog(array $data): void
    {
        $webhookUrl = config('sms.discord_webhook_url');

        if (empty($webhookUrl)) {
            if (!self::$webhookWarned) {
                self::$webhookWarned = true;
                LaravelLog::warning('laravelbdsms: the discord log driver is enabled but
                sms.discord_webhook_url is empty, so sms logs are not being posted to discord.');
            }
            return;
        }

        try {
            $client = new Client(['timeout' => 10.0]);
            $client->request('POST', $webhookUrl, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => ['embeds' => [$this->embed($data)]],
            ]);
        } catch (Throwable $e) {
            LaravelLog::warning('laravelbdsms: could not post the sms log to discord', [
                'reason' => $e->getMessage(),
                'log' => $data,
            ]);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function embed(array $data): array
    {
        $request = $this->decode($data['request_json'] ?? null);
        $provider = (string)($data['provider'] ?? '-');

        $embed = [
            'title' => 'SMS Log',
            'color' => $this->colour($data['response_json'] ?? ''),
            'description' => $this->table($this->rows($data, $request, $provider)),
            'timestamp' => date('c'),
            'footer' => ['text' => 'laravelbdsms . ' . $this->cut($provider, 200)],
            'fields' => [],
        ];

        $message = is_array($request) ? ($request['message'] ?? null) : null;
        if ($message !== null && $message !== '') {
            $embed['fields'][] = [
                'name' => 'Message',
                'value' => $this->block($this->flatten($message), 'text'),
                'inline' => false,
            ];
        }

        $embed['fields'][] = [
            'name' => 'Response',
            'value' => $this->block($this->pretty($data['response_json'] ?? ''), 'json'),
            'inline' => false,
        ];

        //credentials must not leak into a chat channel, so only the key names travel
        if (is_array($request) && !empty($request['config']) && is_array($request['config'])) {
            $embed['fields'][] = [
                'name' => 'Config used (values hidden)',
                'value' => $this->block(implode(', ', array_keys($request['config'])), 'text'),
                'inline' => false,
            ];
        }

        return $embed;
    }

    /**
     * Label => value pairs for the table.
     *
     * @param array $data
     * @param $request
     * @param string $provider
     * @return array
     */
    private function rows(array $data, $request, string $provider): array
    {
        $rows = ['Provider' => $this->shorten($provider)];

        if (is_array($request)) {
            foreach (['mobile', 'number', 'to'] as $key) {
                if (isset($request[$key]) && !isset($rows['Mobile'])) {
                    $rows['Mobile'] = $this->flatten($request[$key]);
                }
            }
        }

        $rows['Status'] = $this->status($data['response_json'] ?? '');
        $rows['Logged at'] = date('Y-m-d H:i:s');

        //anything a caller added beyond the three known keys still shows up
        foreach ($data as $key => $value) {
            if (in_array($key, ['provider', 'request_json', 'response_json'], true)) {
                continue;
            }
            $rows[ucfirst(str_replace('_', ' ', (string)$key))] = $this->flatten($value);
        }

        return $rows;
    }

    /**
     * Render the pairs as an aligned monospace table.
     *
     * @param array $rows
     * @return string
     */
    private function table(array $rows): string
    {
        $lines = [];
        foreach ($rows as $label => $value) {
            $label = $this->cut((string)$label, self::LABEL_WIDTH - 1);
            $pad = str_repeat(' ', max(1, self::LABEL_WIDTH - mb_strlen($label)));
            $lines[] = $label . $pad . '  ' . $this->cut($this->flatten($value), 90);
        }

        return $this->block(implode(self::LINE_BREAK, $lines), 'text', self::DESCRIPTION_LIMIT);
    }

    /**
     * Wrap text in a discord code fence, trimmed to the payload limit.
     *
     * @param string $text
     * @param string $language
     * @param int|null $limit
     * @return string
     */
    private function block(string $text, string $language = 'text', ?int $limit = null): string
    {
        $limit = $limit ?? self::FIELD_LIMIT;
        $fence = str_repeat(chr(96), 3);
        $wrapper = 2 * mb_strlen($fence) + mb_strlen($language) + 2 * mb_strlen(self::LINE_BREAK);
        $room = $limit - $wrapper;

        return $fence . $language . self::LINE_BREAK . $this->cut($text, $room)
            . self::LINE_BREAK . $fence;
    }

    /**
     * @param $json
     * @return array|null
     */
    private function decode($json)
    {
        if (is_array($json)) {
            return $json;
        }
        if (!is_string($json) || $json === '') {
            return null;
        }
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param $value
     * @return string
     */
    private function pretty($value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = $decoded === null ? $value : $decoded;
        }
        if (is_string($value)) {
            return $value;
        }

        return (string)json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Squash any value onto a single line.
     *
     * @param $value
     * @return string
     */
    private function flatten($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_string($value) || is_numeric($value)) {
            return trim(preg_replace('/\s+/', ' ', (string)$value));
        }

        return (string)json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Class basename, so a long namespace does not eat the table.
     *
     * @param string $provider
     * @return string
     */
    private function shorten(string $provider): string
    {
        $position = strrpos($provider, chr(92));

        return $position === false ? $provider : substr($provider, $position + 1);
    }

    /**
     * Best effort read of the gateway verdict. Gateways word this differently, so
     * an unrecognised response is reported as unknown rather than guessed.
     *
     * @param $response
     * @return string
     */
    private function status($response): string
    {
        $text = strtolower($this->flatten($response));

        if (preg_match('/(error|fail|invalid|unauthor|denied|blacklist|insufficient)/', $text)) {
            return 'failed';
        }
        if (preg_match('/(success|delivered|accepted|sent)/', $text)) {
            return 'success';
        }

        return 'unknown';
    }

    /**
     * @param $response
     * @return int
     */
    private function colour($response): int
    {
        $status = $this->status($response);
        if ($status === 'success') {
            return self::COLOUR_SUCCESS;
        }
        if ($status === 'failed') {
            return self::COLOUR_FAILURE;
        }

        return self::COLOUR_NEUTRAL;
    }

    /**
     * @param string $text
     * @param int $limit
     * @return string
     */
    private function cut(string $text, int $limit): string
    {
        if ($limit < 4) {
            return '';
        }

        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit - 3) . '...' : $text;
    }
}
