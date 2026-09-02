# CHANGELOG

## [V2.0.1.0](https://github.com/arif98741/laravelbdsms/releases/tag/V2.0.1.0) - 2026-09-02 17:28:15+00:00

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V2.0.0.0...V2.0.1.0

## [V2.0.1.0-beta](https://github.com/arif98741/laravelbdsms/releases/tag/V2.0.1.0-beta) - 2026-09-02 17:28:15+00:00

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V2.0.0.0...V2.0.1.0-beta

## [V2.0.0.0](https://github.com/arif98741/laravelbdsms/releases/tag/V2.0.0.0) - 2026-09-01 08:50:57+00:00

## Upgrade command for version v2.0.0.0
<pre>composer require xenon/laravelbdsms:^2.0</pre>

## What's New

### Discord log driver

`log_driver` can now post every SMS log straight into a Discord channel through an incoming webhook.

```php
// config/sms.php
'sms_log' => true,
'log_driver' => ['database', 'discord'],
'discord_webhook_url' => env('SMS_LOG_DISCORD_WEBHOOK_URL', ''),
```

```dotenv
SMS_LOG_DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/xxxxx/yyyyy
```

Each log arrives as an embed containing a small table (provider, recipient, status, time) plus the message
and the gateway response. Green when the gateway reports success, red when it reports a failure.

- **Credential values are never sent to Discord** — only the names of the config keys that were used.
- A missing, unreachable or rate limited webhook **never fails the SMS**. The reason is written to
  `storage/logs/laravel.log`.

### `log_driver` accepts a list of drivers

A log can now go to several destinations at once. The old single-string form keeps working exactly as before,
so no existing configuration needs to change.

```php
'log_driver' => 'database',                 // still valid
'log_driver' => ['database', 'discord'],    // new
```

Each driver is also isolated now: one failing driver no longer stops the others.

## Bug Fixes

### Sender state leaked between sends

`Sender` is a shared instance, and each entry point only set part of its state, so whatever the previous send
left behind carried over. Two consequences, both silent:

- After any `shootWithQueue()`, a later `shoot()` **queued the message instead of sending it**, returned
  `true` instead of a report, and skipped logging. `tries` and `backoff` leaked the same way.
- After any `via()`, a plain `SMS::shoot()` used that provider instead of `default_provider`.

Both are fixed. `shoot()` is now always synchronous, and `via()` returns an instance bound to that provider
without repointing anything else.

```php
SMS::via(Ssl::class)->shootWithQueue('017XXXXXXXX', 'queued');
SMS::via(Ssl::class)->shoot('017XXXXXXXX', 'sent right now');  // really sends now
SMS::shoot('017XXXXXXXX', 'uses default_provider');            // really uses the default
```

An instance returned by `via()` is still reusable, so `$ssl = SMS::via(Ssl::class);` followed by several
`$ssl->shoot(...)` calls all go through `Ssl`.

### `Sender::setHeaders()` had no effect

The headers were stored in private properties that `CustomGateway` read from outside the class, so
`isset()` silently returned `false` and every custom header was dropped — including `Authorization`, which
made bearer-token gateways fail with no clue why. Broken since `V1.0.59.2`.

Custom headers now actually reach the request, and both documented formats are accepted:

```php
$sender->setHeaders(['Authorization' => 'Bearer xxx'], true);
$sender->setHeaders(['Authorization: Bearer xxx'], false);   // also accepted
```

### Recipient number and message text were being put in the URL

Every provider that sends a JSON body was passing its payload **twice**: once as the JSON body and once as
URL query parameters, so phone numbers and message text ended up in request URLs, and therefore in server
access logs and proxies. 18 providers were affected.

`Request::optionsPostRequest()` now drops the query for JSON requests, matching what the GET path already did.

### Providers that ignored the queue

- **`SMSNoc`** bypassed `Request` with its own Guzzle client, so it silently opted out of the queue, retries
  and the shared header handling. It now goes through `Request` and honours `shootWithQueue()`, `tries` and
  `backoff`. The request it sends is otherwise unchanged.
- **`Onnorokom`** talks to a SOAP endpoint, which the queued job cannot carry. Previously a queued send was
  performed synchronously *and* skipped logging. It now raises a clear `RenderException` telling you to use
  `shoot()`. Synchronous sends are unaffected.
- **`DnsBd`** has never been implemented and used to return `null`, so callers believed an SMS had gone out.
  It now throws a `RenderException` naming the problem.

### `Robi` provider was unusable

`Robi` was the only provider missing from `config/sms.php`, so `via(Robi::class)` died with
*"config must be an array"*. Its validation also demanded a `telcom_from` key that the request never sends.

It is now registered with the two credentials it actually uses, and appears in the documentation.

```php
Robi::class => [
    'username' => env('SMS_ROBI_USERNAME', ''),
    'password' => env('SMS_ROBI_PASSWORD', ''),
],
```

Note: `Robi` still has not been verified against the live gateway. If your account needs a sender ID or
masking parameter, that has yet to be added.

### `ZamanIt` reported a null mobile number

`ZamanIt` filled `$data['phone']` while the report builder reads `$data['number']`, so every synchronous send
raised an *Undefined array key* warning and reported the recipient as `null`.

### Queued logs recorded an empty payload

`SendSmsJob` read the request payload from a key that is absent on JSON requests, so those log rows could be
written empty.

## Internal

### Providers deduplicated

All 52 providers carried a byte-identical constructor, and 50 of them repeated the same seven-line getter
preamble and the same report-building tail. All three moved into `AbstractProvider`, removing **1,194 lines**
from the provider directory (4,737 → 3,543) with no change to the requests any provider sends.

Writing a provider is now two short methods:

```php
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

        return $this->respond($this->makeRequest($this->apiEndpoint, $query)->get());
    }

    public function errorException()
    {
        if (!array_key_exists('token', $this->senderObject->getConfig())) {
            throw new ParameterException('token key is absent in configuration');
        }
    }
}
```

`makeRequest()` forwards the sender's queue name, tries and backoff automatically, so a new provider cannot
forget to support the queue — which is what caused the bugs above in the first place.

Log driver selection also moved into a single `LogDispatcher`, shared by the direct send and the queued job.
It used to be duplicated in both, which is why drivers behaved differently on the two paths.

## Upgrade Notes

No configuration changes are required, and no public method was removed. These behaviour changes are worth
knowing about:

| Change | Effect |
|--------|--------|
| `via()` is no longer sticky | A bare `SMS::shoot()` now uses `default_provider`. If you relied on `via()` persisting to later unrelated sends, pass the provider explicitly. |
| `shoot()` is always synchronous | Previously it could queue silently after a `shootWithQueue()`. |
| `setHeaders()` now works | Headers you already pass will start being sent. Its second argument defaults to `true`, which sends the payload as a JSON body — pass `false` to keep form/query encoding. |
| JSON requests no longer send query parameters | If a gateway of yours reads parameters from the URL despite receiving a JSON body, tell us. |
| Logging failures no longer propagate | A broken log driver (for example an unmigrated `lbs_log` table) is now reported in `storage/logs/laravel.log` instead of throwing out of `send()`, which used to report an already-delivered SMS as a failure. |

## Documentation

The README has been rewritten: task-based usage sections, a documented return value and exception list, the
full `Sender` API, logging setup for all three drivers, a known-limitations table, and a guide to writing
your own provider.

## [V2.0.0.0-beta](https://github.com/arif98741/laravelbdsms/releases/tag/V2.0.0.0-beta) - 2026-09-01 07:27:17+00:00

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.67.2...V2.0.0.0-beta

## [V1.0.67.2](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.67.2) - 2026-08-02 12:14:24+00:00

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.67.1...V1.0.67.2

## [V1.0.67.1](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.67.1) - 2026-08-02 12:08:28+00:00

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.67.0...V1.0.67.1

## [V1.0.67.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.67.0) - 2026-06-08 07:48:24+00:00

## What's Changed
* TMSSIct Provider Added by @arif98741 in https://github.com/arif98741/laravelbdsms/pull/102


**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.66.1...V1.0.67.0

## [V1.0.66.1](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.66.1) - 2026-05-19 19:54:17+00:00

## What's Changed
* Fix: Update SMSNoc provider to use official v1 API endpoint by @ashikhasnat in https://github.com/arif98741/laravelbdsms/pull/101

## New Contributors
* @ashikhasnat made their first contribution in https://github.com/arif98741/laravelbdsms/pull/101

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.66.0...V1.0.66.1

## [V1.0.66.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.66.0) - 2026-04-01 12:04:33+00:00

## What's Changed
* bulksms dhaka provider added by @arif98741 in https://github.com/arif98741/laravelbdsms/pull/99


**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.65.4...V1.0.66.0

## [V1.0.65.4](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.65.4) - 2026-02-05 19:00:54+00:00

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.65.3...V1.0.65.4

## [V1.0.65.3](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.65.3) - 2026-02-05 18:41:31+00:00

## What's Changed
* Esms api url changed by @arif98741 in https://github.com/arif98741/laravelbdsms/pull/98


**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.65.2...V1.0.65.3

## [V1.0.65.2](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.65.2) - 2026-02-05 12:49:45+00:00

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.65.1...V1.0.65.2

## [V1.0.65.1](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.65.1) - 2025-12-23 12:54:01+00:00

## What's Changed
* Clarify Reve SMS support (AjuraTech) by @JimNewaz in https://github.com/arif98741/laravelbdsms/pull/97

## New Contributors
* @JimNewaz made their first contribution in https://github.com/arif98741/laravelbdsms/pull/97

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.65.0...V1.0.65.1

## [V1.0.65.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.65.0) - 2025-06-03 08:13:05+00:00

## What's Changed
* Eamarseba provider added by @arif98741 in https://github.com/arif98741/laravelbdsms/pull/95


**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.64.7...V1.0.65.0

## [V1.0.64.7](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.64.7) - 2025-03-05 15:16:54+00:00

## [V1.0.64.6](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.64.6) - 2025-01-30 09:13:48

## [V1.0.64.5](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.64.5) - 2025-01-28 04:51:55

## [V1.0.64.4](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.64.4) - 2025-01-27 18:19:47

## [V1.0.64.3](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.64.3) - 2025-01-10 07:07:08

## [V1.0.64.2](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.64.2) - 2024-12-22 08:51:56

## [V1.0.64.1](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.64.1) - 2024-10-28 16:54:55

## [V1.0.64.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.64.0) - 2024-09-30 21:32:56

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.63.0...V1.0.64.0

## [V1.0.63.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.63.0) - 2024-09-18 12:35:07

## [V1.0.62.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.62.0) - 2024-09-18 10:30:57

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.61.0...V1.0.62.0

## [V1.0.61.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.61.0) - 2024-09-15 18:16:19

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.60.3...V1.0.61.0

## [V1.0.60.3](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.60.3) - 2024-08-10 06:31:50

## [V1.0.60.2](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.60.2) - 2024-06-25 13:03:00

## [V1.0.60.1](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.60.1) - 2024-05-24 16:45:53

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.59.3...V1.0.60.0

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.59.3...V1.0.60.1

## [V1.0.59.3](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.59.3) - 2024-05-24 11:35:07

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.59.2...V1.0.59.3

### Bug Fixes

- general:
  - log-driver undefined index issue solved ([5178433](https://github.com/arif98741/laravelbdsms/commit/5178433c9e8db4d6e4d09b530c52dffdc3c9c849))

## [V1.0.59.2](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.59.2) - 2024-05-24 11:04:23

## [V1.0.59.1](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.59.1) - 2024-05-21 12:40:49

## [V1.0.59.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.59.0) - 2024-05-14 13:41:13

## [v1.0.58.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.58.1) - 2024-05-05 06:25:16

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/V1.0.58.0...v1.0.58.1

## [V1.0.58.0](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.58.0) - 2024-04-10 07:31:28

## [V1.0.57.2](https://github.com/arif98741/laravelbdsms/releases/tag/V1.0.57.2) - 2024-03-29 05:43:54

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.57.1...V1.0.57.2

## [v1.0.57.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.57.1) - 2024-03-16 19:09:11

## [v1.0.57.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.57.0) - 2023-12-06 16:45:31

## [v1.0.56.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.56.0) - 2023-11-02 11:12:21

## [v1.0.56.0-alpha](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.56.0-alpha) - 2023-11-02 10:54:20

## [v1.0.55.0-alpha](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.55.0-alpha) - 2023-10-26 16:47:07

## [v1.0.54.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.54.0) - 2023-10-04 20:03:18

## [v1.0.53.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.53.0) - 2023-10-04 18:42:06

## [v1.0.52.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.52.0) - 2023-09-14 09:23:29+00:00

## What's Changed
* Feat lpeek sms gateway by @sim8568X in https://github.com/arif98741/laravelbdsms/pull/48
* Lpeek Sms Gateway by @sim8568X in https://github.com/arif98741/laravelbdsms/pull/49


**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.51.0...v1.0.52.0

## [v1.0.52.0-beta](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.52.0-beta) - 2023-09-14 09:23:29

## [v1.0.51.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.51.0) - 2023-07-26 17:34:29

## [v1.0.51.0-beta](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.51.0-beta) - 2023-07-26 12:16:28

## [v1.0.50.5](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.50.5) - 2023-06-20 05:41:25

## [v1.0.50.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.50.2) - 2023-06-19 15:14:21

## [v1.0.50.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.50.1) - 2023-06-02 21:23:32

## [v1.0.50.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.50.0) - 2023-05-16 07:19:49

## [v1.0.49.3](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.49.3) - 2023-04-03 09:00:57

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.49.2...v1.0.49.3

## [v1.0.49.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.49.2) - 2023-04-03 08:50:16

*No description*

## [v1.0.49.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.49.1) - 2023-04-03 08:40:05

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.47.0...v1.0.49.1

## [v1.0.49.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.49.0) - 2023-03-16 08:04:21

Merge pull request #31 from sim8568X/master

Merge Request for Adding SmsQ Gateway

## [v1.0.48.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.48.1) - 2023-02-18 14:08:34

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.47.2...v1.0.48.1

### Bug Fixes

- general:
  - fix conflicts and update readme file ([2d41524](https://github.com/arif98741/laravelbdsms/commit/2d41524d4cb071a3e8962be866cab95530df66a4))

## [v1.0.47.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.47.2) - 2023-02-14 19:49:26

## [v1.0.47.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.47.0) - 2023-02-06 18:12:15

Fix conflicts

## [v1.0.46.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.46.2) - 2022-12-22 19:15:10

schema lbs_log table export added

### Bug Fixes

- general:
  - change sms provider list configuration syntax error ([f980881](https://github.com/arif98741/laravelbdsms/commit/f980881a42c34fc6e06dbc669f843ecabded86c7))

## [v1.0.46.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.46.1) - 2022-12-05 10:10:47

fix conflicts

### Bug Fixes

- general:
  - fix conflicts ([239218b](https://github.com/arif98741/laravelbdsms/commit/239218bad5d22ebcc89f77b890522653aac4e22b)) ([#19](https://github.com/arif98741/laravelbdsms/pull/19))

## [v1.0.46.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.46.0) - 2022-12-05 10:04:07

fix: conflicts removed

### Bug Fixes

- general:
  - conflicts removed ([bdb1ec9](https://github.com/arif98741/laravelbdsms/commit/bdb1ec94bb7a22f46a97c0b347506daa78958f5c))

## [v1.0.45.5](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.45.5) - 2022-12-04 12:28:38

Merge pull request #16 from arif98741/dev

Fix minor issues for BulksmsBD

## [v1.0.45.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.45.2) - 2022-12-04 12:20:54

## [v1.0.45.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.45.1) - 2022-12-04 12:16:31

1. Change Bulksmsbd Api Endpoint
2. Change sms parameter

## [v1.0.45.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.45.0) - 2022-11-29 14:05:16

*No description*

## [v1.0.44.3](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.44.3) - 2022-09-28 06:26:21

fix type issue

### Bug Fixes

- general:
  - fix type issue ([c6ad313](https://github.com/arif98741/laravelbdsms/commit/c6ad313ee300e67dbe2c17f88d334a4f78c3faa1))

## [v1.0.44.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.44.1) - 2022-09-27 14:01:22

## [v1.0.44.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.44.2) - 2022-09-27 14:04:34

Merge branch 'smsnet24-gateway'

## [v1.0.44.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.44.0) - 2022-09-27 13:57:28

SmsNet24 Gateway Added

## [v1.0.43.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.43.0) - 2022-09-26 11:22:33

Merge pull request #13 from arif98741/dev

dev

## [v1.0.42.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.42.1) - 2022-09-24 21:37:52

fix queue insert log based on config status

### Bug Fixes

- general:
  - fix queue insert log based on config status ([dbe4fe7](https://github.com/arif98741/laravelbdsms/commit/dbe4fe73e9fd75710813b96a233fdac8f5357162))

## [v1.0.42.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.42.0) - 2022-09-24 20:49:06

## [v1.0.41.5](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.41.5) - 2022-08-14 22:22:47

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.41.2...v1.0.41.5

## [v1.0.41.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.41.1) - 2022-08-13 14:37:38

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.41.0...v1.0.41.1

### Bug Fixes

- general:
  - fix ssl vefication error from local environment ([d5a800e](https://github.com/arif98741/laravelbdsms/commit/d5a800e2f604b54a42bbdf66e107d203c2569a67))

## [v1.0.41.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.41.2) - 2022-08-13 14:39:37

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.41.1...v1.0.41.2

## [v1.0.41.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.41.0) - 2022-08-04 09:02:08

*No description*

## [v1.0.41-beta](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.41-beta) - 2022-08-04 06:38:19+00:00

*No description*

## [v1.0.41-dev](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.41-dev) - 2022-08-04 06:38:19

*No description*

## [v1.0.40.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.40.0) - 2022-06-16 09:06:52

*No description*

## [v1.0.39.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.39.0) - 2022-06-15 18:57:00

**Full Changelog**: https://github.com/arif98741/laravelbdsms/compare/v1.0.38.5...v1.0.39.0

## [v1.0.38.5](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.38.5) - 2022-05-18 19:02:16

## [v1.0.38.4](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.38.4) - 2022-04-09 00:48:11

*No description*

## [v1.0.38.3](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.38.3) - 2022-04-09 00:36:39

1. Add Mobishasra SMS provider to list. Though it is international sms sending provider. But we have used here for sending it to only Bangladesh. 

## [v1.0.38.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.38.2) - 2022-02-21 19:59:57

1. Added right query params to BulkSmsBD Provider

special thanks to @Islamshawon71 for creating issues and mention the bug.

Issue was discussed here. [You can click this link](https://github.com/arif98741/laravelbdsms/issues/6)


### Bug Fixes

- general:
  - fix bulksmsbd messaging query params with right ones ([39e3d04](https://github.com/arif98741/laravelbdsms/commit/39e3d041a6fa995f802db6798246a489617ee35e))

## [v1.0.38.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.38.1) - 2022-02-08 06:58:54

*No description*

## [v1.0.38.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.38.0) - 2022-01-30 11:58:15

Added banglalink sms gateway to the list

## [v1.0.37.3](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.37.3) - 2022-01-26 12:51:57

*No description*

## [v1.0.37.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.37.2) - 2022-01-25 19:58:53

*No description*

## [v1.0.37.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.37.1) - 2022-01-24 05:22:28

*No description*

## [v1.0.37.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.37.0) - 2022-01-24 04:57:03

*No description*

## [v1.0.36.7](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36.7) - 2022-01-24 04:39:28

*No description*

## [v1.0.36.6](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36.6) - 2021-12-26 15:01:38

*No description*

## [v1.0.36.5](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36.5) - 2021-10-18 11:10:58

*No description*

### Bug Fixes

- general:
  - fix minor issue for Mobireach config ([e23df47](https://github.com/arif98741/laravelbdsms/commit/e23df4787c47bf328a39cc8d9584f453693514b7))

## [v1.0.36.4](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36.4) - 2021-10-18 08:46:06

*No description*

## [v1.0.36.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36.1) - 2021-10-11 05:27:10

*No description*

## [v1.0.36](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36) - 2021-10-11 05:00:54

Added Logger
Changed Readme
Rearrange code

## [v1.0.36.3](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36.3) - 2021-10-11 18:35:57

*No description*

## [v1.0.36.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36.2) - 2021-10-11 11:48:34

*No description*

## [v1.0.36-rc1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.36-rc1) - 2021-10-11 04:14:45

*No description*

## [v1.0.35-rc-2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.35-rc-2) - 2021-10-11 04:14:45

*No description*

## [v1.0.35-rc1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.35-rc1) - 2021-10-09 10:18:26

*No description*

### Bug Fixes

- general:
  - fix minor issues and add azuratech support ([f936e5d](https://github.com/arif98741/laravelbdsms/commit/f936e5d17116f3c722750825136f77e0a95c3e74))

## [v1.0.34](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.34) - 2021-10-07 09:21:00

1. Fix Minor Issues
2. Add Ajuratech Provider Support

## [v1.0.33](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.33) - 2021-10-06 08:02:08

*No description*

## [v1.0.31-alpha](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.31-alpha) - 2021-10-06 04:42:00

*No description*

## [v1.0.30](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.30) - 2021-09-09 05:27:26

*No description*

### Bug Fixes

- general:
  - fix  minor error for tense provider ([e0da717](https://github.com/arif98741/laravelbdsms/commit/e0da7172e211d513c7dfd2471a229c5533df09db))

## [v1.0.29](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.29) - 2021-09-01 11:51:23

v1.0.29
is for fixing bug of sending sms using masking.

## [v1.0.27](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.27) - 2021-09-01 11:46:19

*No description*

## [v1.0.26](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.26) - 2021-08-29 04:23:29

*No description*

## [v1.0.25](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.25) - 2021-07-26 08:02:02

* Add Tense Provider Support 

## [v1.0.24](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.24) - 2021-07-26 08:02:02

*No description*

## [v1.0.23](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.23) - 2021-07-11 20:12:23

*No description*

## [v1.0.19](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.19) - 2021-07-11 12:53:49

*No description*

## [v1.0.18](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.18) - 2021-07-10 07:13:40

*No description*

## [v1.0.17](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.17) - 2021-07-10 06:35:39

*No description*

## [v1.0.16](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.16) - 2021-07-10 06:35:39

*No description*

## [v1.0.15](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.15) - 2021-07-10 06:01:07

*No description*

## [v1.0.14](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.14) - 2021-07-09 17:37:32

*No description*

### Bug Fixes

- general:
  - fix minor error ([43b5ec1](https://github.com/arif98741/laravelbdsms/commit/43b5ec18c3aeb05826f1ee4928a0ea0647db83c7))

## [v1.0.13](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.13) - 2021-07-08 12:41:29

*No description*

## [v1.0.12](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.12) - 2021-07-06 22:24:16

*No description*

## [v1.0.9](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.9) - 2021-07-06 21:44:32

*No description*

### Bug Fixes

- general:
  - fix error ([b357428](https://github.com/arif98741/laravelbdsms/commit/b357428782662b3b1425a3f07913704498473f20))

## [v1.0.8](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.8) - 2021-07-06 17:30:14

*No description*

## [v1.0.7](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.7) - 2021-07-06 08:07:41

*No description*

## [v1.0.6](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.6) - 2021-07-06 07:40:40

*No description*

## [v1.0.5](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.5) - 2021-07-06 07:15:55

*No description*

## [v1.0.3](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.3) - 2021-06-29 05:12:46

*No description*

## [v1.0.2](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.2) - 2021-06-28 18:31:32

*No description*

## [v1.0.1](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.1) - 2021-06-28 10:14:50

*No description*

## [v1.0.0](https://github.com/arif98741/laravelbdsms/releases/tag/v1.0.0) - 2021-06-28 09:55:01

Xenon\LaravelBDSms is a sms gateway package for sending text message to Bangladeshi mobile numbers using several gateways.

\* *This CHANGELOG was automatically generated by [auto-generate-changelog](https://github.com/BobAnkh/auto-generate-changelog)*
