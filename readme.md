Xenon\LaravelBDSms is a sms gateway package for sending text message to Bangladeshi mobile numbers using several
gateways for <strong>Laravel Framework</strong>. You can watch installation process [from youtube](https://youtu.be/i2wjLNoIvIo).

<p><img src="https://img.shields.io/github/issues/arif98741/laravelbdsms">
<img src="https://img.shields.io/github/forks/arif98741/laravelbdsms">
<img src="https://img.shields.io/github/stars/arif98741/laravelbdsms">
   <img src="https://img.shields.io/github/license/arif98741/laravelbdsms">
</p>

<!-- TOC -->
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
  - [Send with the default provider](#send-with-the-default-provider)
  - [Choose a provider per message](#choose-a-provider-per-message)
  - [Send through a queue](#send-through-a-queue)
  - [What a send returns](#what-a-send-returns)
  - [Sending to more than one number](#sending-to-more-than-one-number)
- [Using the Sender object directly](#using-the-sender-object-directly)
  - [Custom gateway](#custom-gateway)
- [Logging](#logging)
  - [database driver](#database-driver)
  - [file driver](#file-driver)
  - [discord driver](#discord-driver)
  - [Reading the log](#reading-the-log)
- [Supported SMS gateways](#supported-sms-gateways)
  - [Known limitations](#known-limitations)
- [Writing your own provider](#writing-your-own-provider)
- [Contributing](#contributing)
<!-- TOC -->

# Requirements

| Requirement | Version                  |
|-------------|--------------------------|
| PHP         | `^8.0`                   |
| Laravel     | 8+ (implied by the PHP 8 requirement) |
| Extensions  | `ext-json`, `ext-curl`   |
| HTTP client | `guzzlehttp/guzzle` `^6.3` or `^7.3` |

# Installation

**Step 1** — require the package:

```bash
composer require xenon/laravelbdsms
```

**Step 2** — publish the config and the migration:

```bash
php artisan vendor:publish --provider=Xenon\LaravelBDSms\LaravelBDSmsServiceProvider --tag="config"
php artisan vendor:publish --provider=Xenon\LaravelBDSms\LaravelBDSmsServiceProvider --tag="migrations"
php artisan migrate
```

This creates `config/sms.php` and the `lbs_log` table. The migration is only needed if you intend to use the
`database` log driver.

**Step 3** — pick your gateway in `config/sms.php` and put its credentials in `.env`. Every provider's env
variable names are listed in `config/sms.php`:

```php
'default_provider' => env('SMS_DEFAULT_PROVIDER', Ssl::class),
```

```dotenv
SMS_SSL_API_TOKEN=your_token
SMS_SSL_SID=your_sid
SMS_SSL_CSMS_ID=your_csms_id
```

**Step 4** — refresh the config cache:

```bash
php artisan config:cache
```

The service provider is auto-discovered, so no manual registration is required.

# Usage

## Send with the default provider

`shoot()` sends immediately and uses the `default_provider` from `config/sms.php`.

```php
use Xenon\LaravelBDSms\Facades\SMS;

$response = SMS::shoot('017XXYYZZAA', 'helloooooooo boss!');
```

The `LaravelBDSms` alias points at the same facade:

```php
use LaravelBDSms;

LaravelBDSms::shoot('017XXYYZZAA', 'helloooooooo boss!');
```

## Choose a provider per message

`via()` selects the gateway for the message you are about to send:

```php
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Provider\Ssl;

$response = SMS::via(Ssl::class)->shoot('017XXYYZZAA', 'helloooooooo boss!');
```

`via()` returns an instance bound to that provider, so you can reuse it:

```php
$ssl = SMS::via(Ssl::class);
$ssl->shoot('017XXYYZZAA', 'first message');
$ssl->shoot('018XXYYZZAA', 'second message');   // still goes through Ssl
```

A plain `SMS::shoot()` always uses `default_provider`, whether or not `via()` was called earlier in the
request. The provider you pass to `via()` never leaks into unrelated sends.

The class name may also be given as a short string:

```php
SMS::via('Ssl')->shoot('017XXYYZZAA', 'helloooooooo boss!');
```

## Send through a queue

`shootWithQueue()` hands the gateway call to a queued job instead of sending inline. Make sure your queue
connection and `jobs` table are configured.

```php
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Provider\Ssl;

SMS::via(Ssl::class)->shootWithQueue('01XXXXXXXXX', 'test sms');

// queue name, retry attempts and retry delay in seconds
SMS::via(Ssl::class)->shootWithQueue('01XXXXXXXXX', 'test sms', 'sms', 5, 90);
```

| Parameter    | Default     | Meaning                                    |
|--------------|-------------|--------------------------------------------|
| `$queueName` | `'default'` | Queue the job is pushed onto               |
| `$tries`     | `3`         | How many times the job may be attempted    |
| `$backoff`   | `60`        | Seconds to wait before retrying            |

A queued send returns `true` as soon as the job is dispatched — the gateway response is not available yet, so
it is written to the log when the worker runs. `shoot()` and `shootWithQueue()` never affect each other:
`shoot()` is always synchronous, even if a queued send happened earlier in the same process.

## What a send returns

A synchronous send returns the gateway response wrapped in a JSON report:

```php
$response = SMS::via(Ssl::class)->shoot('017XXYYZZAA', 'helloooooooo boss!');

// {
//     "status": "response",
//     "response": "{\"status\":\"SUCCESS\",\"status_code\":200}",
//     "provider": "Xenon\\LaravelBDSms\\Provider\\Ssl",
//     "send_time": "2026-09-01 08:03:23",
//     "mobile": "017XXYYZZAA",
//     "message": "helloooooooo boss!"
// }
```

`response` holds the gateway's own reply verbatim, so its shape differs per gateway. Decode the report and
inspect `response` to decide whether the gateway accepted the message:

```php
$report = json_decode($response, true);
$gateway = json_decode($report['response'], true);
```

A failed HTTP call throws `Xenon\LaravelBDSms\Handler\RenderException`, and a configuration problem throws
`Xenon\LaravelBDSms\Handler\ParameterException`. Both extend `Exception`:

```php
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;

try {
    SMS::via(Ssl::class)->shoot('017XXYYZZAA', 'helloooooooo boss!');
} catch (ParameterException $e) {
    // a credential is missing from config/sms.php
} catch (RenderException $e) {
    // the gateway could not be reached
}
```

## Sending to more than one number

The facade takes a single number per call. To send to several numbers in one request, use the `Sender`
object with a provider whose **Support Multiple** column says `Yes`:

```php
use Xenon\LaravelBDSms\Provider\Alpha;
use Xenon\LaravelBDSms\Sender;

$sender = Sender::getInstance();
$sender->setProvider(Alpha::class);
$sender->setConfig(['api_key' => 'your api key']);
$sender->setMobile(['017XXYYZZAA', '018XXYYZZAA']);
$sender->setMessage('helloooooooo boss!');
$sender->setQueue(false);

$response = $sender->send();
```

For every other provider, loop over the numbers and send one message each.

# Using the Sender object directly

If you want full control, and no facade or published config in the way, drive the `Sender` yourself:

```php
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Sender;

$sender = Sender::getInstance();
$sender->setProvider(Ssl::class);        // change this provider class according to need
$sender->setMobile('017XXYYZZAA');
$sender->setMessage('helloooooooo boss!');
$sender->setQueue(false);                // true to dispatch the gateway call as a job
$sender->setConfig([
    'api_token' => 'api token goes here',
    'sid' => 'text',
    'csms_id' => 'sender_id',
]);

$response = $sender->send();
```

`Sender::getInstance()` returns a shared instance, and every setter returns it so calls can be chained.
Values you set stay set until you change them, so set the ones that matter for each send.

| Method                                | Purpose                                                     |
|---------------------------------------|-------------------------------------------------------------|
| `setProvider(string $class)`          | Gateway to send through                                     |
| `setConfig(array $config)`            | Credentials for that gateway                                |
| `setMobile(string\|array $mobile)`    | Recipient, or recipients where the gateway supports it      |
| `setMessage(string $text)`            | Message body                                                |
| `setQueue(bool $queue)`               | Dispatch the gateway call as a job instead of sending inline |
| `setQueueName(string $name)`          | Queue to dispatch onto                                      |
| `setTries(int $tries)`                | Job attempts                                                |
| `setBackoff(int $seconds)`            | Seconds between job attempts                                |
| `setUrl(string $url)`                 | Endpoint, for `CustomGateway`                                |
| `setMethod(string $method)`           | `get` or `post`, for `CustomGateway`                         |
| `setHeaders(array $h, bool $json)`    | Extra request headers, for `CustomGateway`                    |
| `send()`                              | Validate, send, and log                                     |

## Custom gateway

If your gateway is not in the list, `CustomGateway` lets you describe the request yourself. Whatever you pass
to `setConfig()` becomes the request payload:

```php
use Xenon\LaravelBDSms\Provider\CustomGateway;
use Xenon\LaravelBDSms\Sender;

$sender = Sender::getInstance();
$sender->setProvider(CustomGateway::class);
$sender->setUrl('https://your_custom_gateway_provider_url_here')
    ->setMethod('post')
    ->setHeaders([
        'Authorization' => 'Bearer xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    ], true);
$sender->setMobile('017XXYYZZAA');
$sender->setMessage('text message goes here');
$sender->setQueue(false);
$sender->setConfig([
    'MsgType' => 'TEXT',
    'masking' => 'sample',
    'userName' => 'test_user',
    'message' => 'test message',
    'receiver' => '017xxxxxxxxxx',
]);

echo $sender->send();
```

The second argument of `setHeaders()` controls how the payload is encoded: `true` sends it as a JSON body,
`false` sends it as query or form parameters. Headers may be given as `name => value` pairs, or as
`'Name: value'` lines:

```php
$sender->setHeaders(['Authorization' => 'Bearer xxx'], true);
$sender->setHeaders(['Authorization: Bearer xxx'], false);   // also accepted
```

# Logging

Every request and response can be recorded. Turn it on in `config/sms.php`:

```php
'sms_log' => true,
```

Then choose where the log goes. `log_driver` takes one driver name, or a list of them so a log can go to
several places at once:

```php
'log_driver' => 'database',                 // one driver
'log_driver' => ['database', 'discord'],    // or several
```

| Driver     | Destination                                              |
|------------|----------------------------------------------------------|
| `database` | `lbs_log` table                                          |
| `file`     | `storage/logs/laravel.log`                               |
| `discord`  | a discord channel, through an incoming webhook           |

A driver that fails never fails the send — the message has already gone out by the time logging happens, so
the problem is reported in `storage/logs/laravel.log` and the remaining drivers still run.

## database driver

```php
'log_driver' => 'database',
```

Run `php artisan migrate` so the `lbs_log` table exists.

## file driver

```php
'log_driver' => 'file',
```

Entries are written to `storage/logs/laravel.log` under the `laravelbdsms` label.

## discord driver

```php
'log_driver' => ['discord'],
```

1. In your discord channel, open **Channel Settings → Integrations → Webhooks** and create an incoming webhook.
2. Put the webhook URL in `.env`:

```dotenv
SMS_LOG_DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/xxxxx/yyyyy
```

Each log arrives as an embed: a small table with the provider, recipient, status and time, followed by the
message and the gateway response. The embed is green when the gateway reports success and red when it
reports a failure.

Credential **values** are never sent to discord, only the names of the config keys that were used. If the
webhook is missing, unreachable or rate limited, the send still succeeds and the reason is written to
`storage/logs/laravel.log`.

## Reading the log

```php
use Xenon\LaravelBDSms\Facades\Logger;

Logger::viewLastLog();          // most recent entry
Logger::viewAllLog();           // every entry
Logger::logByProvider(Xenon\LaravelBDSms\Provider\Ssl::class);
Logger::total();                // number of entries
Logger::clearLog();             // empty the table
```

These read the `lbs_log` table, so they apply to the `database` driver.

# Supported SMS gateways

Credentials for each gateway are configured in `config/sms.php`, and the matching `.env` variable names are
listed there.

| Provider            | Credentials  Required <br>    For Sending SMS                     | Support Multiple | Status         | Comment                                                  | Contact |
|---------------------|-------------------------------------------------------------------|------------------|----------------|----------------------------------------------------------|---------|
| AjuraTech           | apikey, secretkey , callerID                                      | -                | Done           | Uses Reve SMS API (smpp.revesms.com)                    | -       |
| Adn                 | api_key, api_secret, messsage_type, request_type                  | -                | Done           | -                                                        | -       |
| Alpha               | api_key                                                           | Yes              | Done           | -                                                        | -       |
| Banglalink          | userID, passwd , sender                                           | -                | Done           | -                                                        | -       |
| BDBulkSMS           | token                                                             | -                | Done           | -                                                        | -       |
| BoomCast            | masking  , userName ,   password                                  | -                | Done           | -                                                        | -       |
| BulksmsBD           | api_key,senderid                                                  | -                | Done           | -                                                        | -       |
| BulkSmsDhaka        | api_key, callerID                                                 | -                | Done           | -                                                        | -       |
| CustomGateway       | provide necessary token/api_key/others based on requirements      | -                | Done           | Be careful using this and test based on several scenario | -       |
| DhorolaSms          | apikey, sender                                                    | -                | Done           | -                                                        | -       |
| DianaHost           | api_key, type, senderid                                           | -                | Done           | -                                                        | -       |
| DianaSMS            | ApiKey, ClientId, SenderId                                        | -                | Done           | -                                                        | -       |
| DurjoySoft          | ApiKey, SenderID                                                  | -                | Done           | -                                                        | -       |
| EAmarseba          | x-app-key, x-app-secret, is_masking, masking_name                 | -                | Done           | -                                                        | -       |
| ElitBuzz            | api_key, type, senderid, type                                     | -                | Done           | not tested yet in live                                   | -       |
| Esms                | api_token, sender_id                                              | -                | Done           | -                                                        | -       |
| Grameenphone        | username, password, messagetype                                   | -                | Done           | not tested yet in live                                   | -       |
| Infobip             | user, password                                                    | -                | Done           | not tested yet in live                                   | -       |
| Khudebarta          | api_key, sender_id                                                | -                | Done           | not tested yet in live                                   | -       |
| Lpeek               | acode, apiKey, requestID, masking                                 | -                | Done           | -                                                        | -       |
| MDL                 | api_key, type, senderid                                           | -                | Done           | not tested yet in live                                   | -       |
| Metronet            | api_key, mask                                                     |                  | Done           | -                                                        | -       |
| MimSms              | ApiKey, UserName, SenderName                                      | -                | Done           | -                                                        | -       |
| Mobireach           | Username,Password, From                                           | -                | Done           | -                                                        | -       |
| Muthofun            | sender_id                                                         | Yes              | Done           | -                                                        | -       |
| NovocomBD           | ApiKey , ClientId   , SenderId                                    | -                | Done           | -                                                        | -       |
| OnnoRokomSMS        | userName, userPassword, type, maskName, campaignName              | -                | Done           | not tested yet in live                                   | -       |
| QuickSms            | api_key, senderid, type,scheduledDateTime                         | -                | Done           | not tested yet in live                                   | -       |
| RedmoITSms          | api_token, sender_id                                              | -                | Support closed | -                                                        |
| Reve SMS            | apikey, secretkey , callerID                                      | -                | Done           | Use AjuraTech provider for the Reve SMS                      | -       |
| Robi                | username, password                                                | -                | Done           | not tested yet in live                                       | -       |
| SendMySms           | user, closed                                                      | -                | Done           | tested in live                                           |
| SmartLabSMS         | user, password, sender                                            | -                | Done           | -                                                        | -       |
| Sms4BD              | publickey, privatekey, type,sender, delay                         | -                | Done           | -                                                        | -       |
| SmsBangladesh       | user, password, from                                              | -                | Done           | -                                                        | -       |
| SmsinBD             | api_token, senderid                                               | -                | Done           |                                                          | -       |
| SMS.net.bd          | api_key                                                           | -                | Done           |                                                          | -       |
| SmsQ                | sender_id, client_id, api_key                                     | -                | Done           |                                                          | -       |
| SMSNet24            | user_id, user_password, route_id(optional), sms_type_id(optional) | -                | Done           | -                                                        |         |
| SmsNoc              | sender_id, bearer_token                                           | -                | Done           | -                                                        |         |
| SongBird            | apikey, secretkey, callerID                                       | -                | Done           | -                                                        |         |
| Sslsms              | api_token, sid, csms_id                                           | Yes              | Done           | -                                                        | -       |
| Tense               | user, password, campaign, masking                                 | -                | Done           | -                                                        | -       |
| TmssIct             | api_key, request_type, message_type, campaign_title               | Yes              | Done           | Endpoint: `/api/v001/sent_sms` (https://sms.tmssict.com) | -       |
| Twenty4BulkSms      | api_key, sender_id,user_email                                     | -                | Done           | -                                                        | -       |
| TwentyFourBulkSmsBD | customer_id, api_key                                              | -                | Done           | -                                                        | -       |
| Trubosms            | api_token, sender_id                                              | -                | Done           | -                                                        | -       |
| Viatech             | api_key, mask                                                     | -                | Done           | -                                                        | -       |
| WinText             | token, messagetype, ismasking, masking                            | -                | Done           | -                                                        | -       |
| ZamanIT             | api_key, senderid,type                                            | -                | Done           | -                                                        | -       |
| ZendSms             | api_key, sender_id, client_ref                                    | -                | Done           | not tested yet in live                                   | -       |

## Known limitations

| Provider     | Limitation                                                                                       |
|--------------|--------------------------------------------------------------------------------------------------|
| `DnsBd`      | Not implemented. Selecting it throws a `RenderException` instead of silently sending nothing.      |
| `Onnorokom`  | Talks to a SOAP endpoint, which the queued job cannot carry. Use `shoot()`, not `shootWithQueue()`. Requires the `soap` PHP extension. |
| `Robi`       | Implemented but never verified against the live gateway.                                          |

Gateways marked *not tested yet in live* in the table above were written from the provider's documentation
but have not been confirmed against a real account. Reports are welcome.

# Writing your own provider

A provider only has to describe its own request. `AbstractProvider` supplies the constructor, forwards the
queue settings and builds the report, so a new gateway is usually two short methods:

```php
namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\ParameterException;

class MyGateway extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.mygateway.com/send';

    public function sendRequest()
    {
        $config = $this->senderObject->getConfig();

        $query = [
            'token' => $config['token'],
            'to' => $this->senderObject->getMobile(),
            'message' => $this->senderObject->getMessage(),
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);

        return $this->respond($requestObject->get());
    }

    public function errorException()
    {
        if (!array_key_exists('token', $this->senderObject->getConfig())) {
            throw new ParameterException('token key is absent in configuration');
        }
    }
}
```

| Helper                                          | What it does                                                                              |
|-------------------------------------------------|-------------------------------------------------------------------------------------------|
| `makeRequest($url, $query = [], $headers = [])` | Builds the request already carrying the sender's queue name, tries and backoff             |
| `respond($response)`                            | Returns `true` for a queued send, otherwise reads the body and builds the report           |
| `errorException()`                              | Runs before the send; throw here when a required credential is missing                     |

For a JSON body instead of query parameters, set it on the request object:

```php
$requestObject = $this->makeRequest($this->apiEndpoint, $query, ['Authorization' => $config['api_key']]);
$requestObject->setContentTypeJson(true);

return $this->respond($requestObject->post());
```

Finally, register the class and its credentials in `src/Config/sms.php` so `via(MyGateway::class)` can find
its configuration.

# Contributing

We are continuously working in this open source library for adding more Bangladeshi sms gateway. If you feel
something is missing then make an issue regarding that. If you want to contribute in this library, then you
are highly welcome to do that.

For clear documentation read this blog
in [Medium!](https://send-sms-using-laravelbdsms.medium.com/laravel-sms-gateway-package-for-bangladesh-e70af99f2060)
and also you can download several sms providers documentations as pdf from
[this link!](https://github.com/arif98741/laravelbdsms/archive/refs/heads/doc.zip)

### Stargazers
[![Stargazers repo roster for @arif98741/laravelbdsms](https://reporoster.com/stars/arif98741/laravelbdsms)](https://github.com/arif98741/laravelbdsms/stargazers)

### Forkers
[![Forkers repo roster for @arif98741/laravelbdsms](https://reporoster.com/forks/arif98741/laravelbdsms)](https://github.com/arif98741/laravelbdsms/network/members)

### Contributors
<a href="https://github.com/arif98741/laravelbdsms/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=arif98741/laravelbdsms" />
</a>

Special thanks to <br>
[tusharkhan](https://github.com/tusharkhan) <br>
[tusher9352](https://github.com/tusher9352)
