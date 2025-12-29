# LaravelBDSMS - Complete Documentation

## Table of Contents

1. [Introduction](#1-introduction)
2. [Installation](#2-installation)
3. [Configuration](#3-configuration)
4. [Quick Start](#4-quick-start)
5. [Usage Guide](#5-usage-guide)
6. [Queue System](#6-queue-system)
7. [Logging System](#7-logging-system)
8. [Supported Providers](#8-supported-providers)
9. [Custom Gateway](#9-custom-gateway)
10. [Architecture & Internals](#10-architecture--internals)
11. [Creating Custom Providers](#11-creating-custom-providers)
12. [Helper Functions](#12-helper-functions)
13. [Exception Handling](#13-exception-handling)
14. [API Reference](#14-api-reference)
15. [Troubleshooting](#15-troubleshooting)

---

## 1. Introduction

### Overview

**LaravelBDSMS** is a comprehensive Laravel package for sending SMS messages to Bangladeshi mobile numbers. It provides a unified interface to work with 50+ SMS gateway providers commonly used in Bangladesh.

### Package Information

| Property | Value |
|----------|-------|
| Package Name | `xenon/laravelbdsms` |
| Namespace | `Xenon\LaravelBDSms` |
| Author | Ariful Islam (arif98741@gmail.com) |
| License | MIT |
| PHP Version | ^8.0 |
| GitHub | https://github.com/arif98741 |

### Key Features

- **Multi-Provider Support**: 52+ SMS gateways supported
- **Facade Pattern**: Simple, elegant API via `SMS::shoot()`
- **Dynamic Provider Switching**: Change providers on-the-fly with `via()`
- **Bulk SMS**: Send to multiple recipients simultaneously
- **Queue Support**: Asynchronous SMS sending with configurable retries
- **Logging**: Database or file-based request/response logging
- **Custom Gateway**: Flexible API for unsupported providers
- **Phone Validation**: Bangladesh-specific number validation
- **Singleton Pattern**: Efficient resource management

### Requirements

- PHP 8.0 or higher
- Laravel 8.x, 9.x, 10.x, or 11.x
- GuzzleHTTP 6.3+ or 7.3+
- PHP Extensions: `ext-json`, `ext-curl`

---

## 2. Installation

### Step 1: Install via Composer

```bash
composer require xenon/laravelbdsms
```

### Step 2: Publish Configuration & Migration

```bash
php artisan vendor:publish --provider="Xenon\LaravelBDSms\LaravelBDSmsServiceProvider"
```

This publishes:
- `config/sms.php` - Configuration file
- `database/migrations/xxxx_xx_xx_xxxxxx_create_laravelbd_sms_table.php` - Log table migration

### Step 3: Run Migration (Optional - for logging)

```bash
php artisan migrate
```

### Step 4: Configure Environment

Add your SMS provider credentials to `.env`:

```env
# Default Provider
SMS_DEFAULT_PROVIDER=Xenon\LaravelBDSms\Provider\Ssl

# SSL Wireless Credentials
SMS_SSL_API_TOKEN=your_api_token
SMS_SSL_SID=your_sender_id
SMS_SSL_CSMS_ID=your_csms_id
```

### Auto-Discovery

Laravel's package auto-discovery automatically registers:
- Service Provider: `Xenon\LaravelBDSms\LaravelBDSmsServiceProvider`
- Aliases: `SMS`, `LaravelBDSms`

---

## 3. Configuration

### Configuration File: `config/sms.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Logging
    |--------------------------------------------------------------------------
    | Enable/disable SMS request and response logging.
    */
    'sms_log' => false,

    /*
    |--------------------------------------------------------------------------
    | Log Driver
    |--------------------------------------------------------------------------
    | Where to store logs: 'database' (lbs_log table) or 'file' (laravel.log)
    */
    'log_driver' => 'database', // database, file

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    | The default SMS provider used by the SMS facade.
    */
    'default_provider' => env('SMS_DEFAULT_PROVIDER', Ssl::class),

    /*
    |--------------------------------------------------------------------------
    | Provider Credentials
    |--------------------------------------------------------------------------
    | Configuration for each SMS provider.
    */
    'providers' => [
        Ssl::class => [
            'api_token' => env('SMS_SSL_API_TOKEN', ''),
            'sid' => env('SMS_SSL_SID', ''),
            'csms_id' => env('SMS_SSL_CSMS_ID', ''),
            'batch_csms_id' => env('SMS_SSL_BATCH_CSMS_ID', ''),
        ],
        // ... other providers
    ]
];
```

### Environment Variables by Provider

#### SSL Wireless
```env
SMS_SSL_API_TOKEN=your_api_token
SMS_SSL_SID=your_sender_id
SMS_SSL_CSMS_ID=your_csms_id
SMS_SSL_BATCH_CSMS_ID=your_batch_id
```

#### BoomCast
```env
SMS_BOOM_CAST_URL=https://api.boomcast.com
SMS_BOOM_CAST_USERNAME=your_username
SMS_BOOM_CAST_PASSWORD=your_password
SMS_BOOM_CAST_MASKING=your_mask
```

#### Infobip
```env
SMS_INFOBIP_BASE_URL=https://api.infobip.com
SMS_INFOBIP_USER=your_username
SMS_INFOBIP_PASSWORD=your_password
SMS_INFOBIP_FROM=your_sender
```

#### MimSms
```env
SMS_MIM_SMS_SENDER_NAME=your_sender
SMS_MIM_SMS_API_KEY=your_api_key
SMS_MIM_SMS_API_USERNAME=your_username
SMS_MIM_SMS_API_TRANSACTION_TYPE=T
SMS_MIM_SMS_API_CAMPAIGN_ID=campaign_id
SMS_MIM_SMS_API_CAMPAIGN_NAME=campaign_name
```

#### GreenWeb
```env
SMS_GREEN_WEB_TOKEN=your_token
```

#### Onnorokom
```env
SMS_ONNOROKOM_USERNAME=your_username
SMS_ONNOROKOM_PASSWORD=your_password
SMS_ONNOROKOM_TYPE=TEXT
SMS_ONNOROKOM_MASK=your_mask
SMS_ONNOROKOM_CAMPAIGN_NAME=campaign
```

---

## 4. Quick Start

### Send Your First SMS

```php
use Xenon\LaravelBDSms\Facades\SMS;

// Simple SMS
SMS::shoot('017XXXXXXXXX', 'Hello from LaravelBDSMS!');

// Using alias
LaravelBDSms::shoot('017XXXXXXXXX', 'Hello World!');
```

### Send to Multiple Recipients

```php
SMS::shoot(['017XXXXXXXXX', '018XXXXXXXXX', '019XXXXXXXXX'], 'Bulk message to all!');
```

### Switch Provider Dynamically

```php
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Provider\BoomCast;

// Use SSL Wireless
SMS::via(Ssl::class)->shoot('017XXXXXXXXX', 'Via SSL');

// Use BoomCast
SMS::via(BoomCast::class)->shoot('017XXXXXXXXX', 'Via BoomCast');
```

### Queue SMS for Later

```php
// Basic queue (default settings)
SMS::shootWithQueue('017XXXXXXXXX', 'Queued message');

// Custom queue settings
SMS::shootWithQueue(
    '017XXXXXXXXX',          // Mobile number
    'Important message',      // Message text
    'sms_queue',             // Queue name
    5,                       // Retry attempts
    120                      // Backoff seconds
);
```

---

## 5. Usage Guide

### 5.1 Using the SMS Facade

The `SMS` facade provides a clean, expressive API:

```php
use Xenon\LaravelBDSms\Facades\SMS;

// Method 1: Direct shoot
$response = SMS::shoot('017XXXXXXXXX', 'Your message here');

// Method 2: With provider selection
$response = SMS::via(Ssl::class)->shoot('017XXXXXXXXX', 'Message');

// Method 3: Queued sending
$response = SMS::shootWithQueue('017XXXXXXXXX', 'Message');
```

### 5.2 Using the Sender Class Directly

For more control, use the `Sender` class:

```php
use Xenon\LaravelBDSms\Sender;
use Xenon\LaravelBDSms\Provider\Ssl;

$sender = Sender::getInstance();

$sender->setProvider(Ssl::class)
       ->setConfig([
           'api_token' => 'your_token',
           'sid' => 'your_sid',
           'csms_id' => 'your_csms_id',
       ])
       ->setMobile('017XXXXXXXXX')
       ->setMessage('Hello World!');

$response = $sender->send();
```

### 5.3 Response Format

All SMS operations return a JSON response:

```json
{
    "status": "response",
    "response": "<provider_api_response>",
    "provider": "Xenon\\LaravelBDSms\\Provider\\Ssl",
    "send_time": "2024-01-15 14:30:45",
    "mobile": "017XXXXXXXXX",
    "message": "Your message text"
}
```

### 5.4 Phone Number Formats

The package accepts various Bangladeshi number formats:

```php
// All valid formats
SMS::shoot('017XXXXXXXXX', 'Message');      // Local format
SMS::shoot('8801XXXXXXXXX', 'Message');     // With country code
SMS::shoot('+8801XXXXXXXXX', 'Message');    // International format
SMS::shoot('01XXXXXXXXX', 'Message');       // Short format
```

---

## 6. Queue System

### 6.1 Overview

The queue system enables asynchronous SMS sending with automatic retry on failure.

### 6.2 Basic Queue Usage

```php
use Xenon\LaravelBDSms\Facades\SMS;

// Default: 3 retries, 60 second backoff
SMS::shootWithQueue('017XXXXXXXXX', 'Message');
```

### 6.3 Advanced Queue Configuration

```php
SMS::shootWithQueue(
    '017XXXXXXXXX',    // Mobile number
    'Message text',    // Message
    'high_priority',   // Queue name (default: 'default')
    10,                // Max retry attempts (default: 3)
    300                // Backoff in seconds (default: 60)
);
```

### 6.4 With Provider Selection

```php
use Xenon\LaravelBDSms\Provider\BoomCast;

SMS::via(BoomCast::class)->shootWithQueue(
    '017XXXXXXXXX',
    'Urgent message',
    'urgent_sms',
    5,
    30
);
```

### 6.5 Queue Worker Setup

Run the queue worker to process SMS jobs:

```bash
# Process default queue
php artisan queue:work

# Process specific queue
php artisan queue:work --queue=sms_queue

# With retry settings
php artisan queue:work --queue=sms_queue --tries=3 --backoff=60
```

### 6.6 Supervisor Configuration

For production, use Supervisor to manage queue workers:

```ini
[program:laravel-sms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=sms_queue --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/logs/sms-worker.log
```

### 6.7 Queue Flow Diagram

```
shootWithQueue() called
        │
        ▼
┌───────────────────┐
│ Sender sets:      │
│ - queue = true    │
│ - queueName       │
│ - tries           │
│ - backoff         │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Request.post()    │
│ checks queue flag │
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    ▼           ▼
 queue=true  queue=false
    │           │
    ▼           ▼
┌─────────┐ ┌─────────────┐
│dispatch │ │Execute HTTP │
│SendSmsJob│ │request now  │
└────┬────┘ └─────────────┘
     │
     ▼
┌───────────────────┐
│ Job stored in     │
│ jobs table        │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Queue Worker      │
│ picks up job      │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Job.handle()      │
│ executes request  │
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    ▼           ▼
 Success      Failure
    │           │
    ▼           ▼
  Log it    Retry with
            backoff
```

---

## 7. Logging System

### 7.1 Enable Logging

In `config/sms.php`:

```php
'sms_log' => true,
'log_driver' => 'database', // or 'file'
```

### 7.2 Database Logging

Logs are stored in the `lbs_log` table:

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Auto-increment primary key |
| provider | TEXT | Provider class name |
| request_json | TEXT | Request parameters (JSON) |
| response_json | TEXT | API response (JSON) |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Update timestamp |

### 7.3 File Logging

When using file driver, logs go to `storage/logs/laravel.log`:

```
[2024-01-15 14:30:45] local.INFO: laravelbdsms {
    "provider": "Xenon\\LaravelBDSms\\Provider\\Ssl",
    "request_json": {...},
    "response_json": {...}
}
```

### 7.4 Using the Logger Facade

```php
use Xenon\LaravelBDSms\Facades\Logger;

// Get last log entry
$lastLog = Logger::viewLastLog();

// Get all logs
$allLogs = Logger::viewAllLog();

// Get logs by provider
$sslLogs = Logger::logByProvider(Ssl::class);

// Get logs for default provider
$defaultLogs = Logger::logByDefaultProvider();

// Get total log count
$total = Logger::total();

// Clear all logs
Logger::clearLog();

// Convert to array
$array = Logger::toArray($lastLog);
```

### 7.5 Log Model

Access logs via Eloquent:

```php
use Xenon\LaravelBDSms\Models\LaravelBDSmsLog;

// Query logs
$logs = LaravelBDSmsLog::where('provider', 'like', '%Ssl%')
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

---

## 8. Supported Providers

### Complete Provider List (52 Providers)

| # | Provider Class | Description |
|---|----------------|-------------|
| 1 | `Adn` | ADN SMS Bangladesh |
| 2 | `AjuraTech` | Reve SMS API integration |
| 3 | `Alpha` | Alpha SMS Service |
| 4 | `Banglalink` | Banglalink Mobile Operator |
| 5 | `BDBulkSms` | BD Bulk SMS Provider |
| 6 | `BoomCast` | BoomCast SMS with masking |
| 7 | `Brilliant` | Brilliant SMS |
| 8 | `BulkSmsBD` | Bulk SMS BD |
| 9 | `CustomGateway` | Custom API integration |
| 10 | `DianaHost` | Diana Host SMS |
| 11 | `DianaSms` | Diana SMS Provider |
| 12 | `DhorolaSms` | Dhorola SMS |
| 13 | `DnsBd` | DNS BD SMS |
| 14 | `DurjoySoft` | DurjoySoft SMS |
| 15 | `EAmarseba` | E-Amarseba SMS |
| 16 | `ElitBuzz` | ElitBuzz SMS |
| 17 | `Esms` | ESMS Provider |
| 18 | `Grameenphone` | Grameenphone Operator |
| 19 | `GreenWeb` | GreenWeb Token-based SMS |
| 20 | `Infobip` | Infobip International |
| 21 | `Lpeek` | Lpeek SMS |
| 22 | `MDL` | MDL SMS |
| 23 | `Metronet` | Metronet SMS |
| 24 | `MimSms` | Mim SMS with campaigns |
| 25 | `Mobireach` | Mobireach SMS |
| 26 | `Mobishasra` | Mobishasra SMS |
| 27 | `Muthofun` | Muthofun SMS |
| 28 | `NovocomBd` | Novocom BD SMS |
| 29 | `Onnorokom` | Onnorokom SMS |
| 30 | `QuickSms` | Quick SMS |
| 31 | `RedmoItSms` | Redmo IT SMS |
| 32 | `SendMySms` | Send My SMS |
| 33 | `SmartLabSms` | SmartLab SMS |
| 34 | `Sms4BD` | SMS4BD |
| 35 | `SmsBangladesh` | SMS Bangladesh |
| 36 | `SmsinBD` | SMS in BD |
| 37 | `SmsNet24` | SMS Net 24 |
| 38 | `SmsNetBD` | SMS Net BD |
| 39 | `SMSNoc` | SMS NOC |
| 40 | `SmsQ` | SMS Q |
| 41 | `SongBird` | SongBird SMS |
| 42 | `Ssl` | SSL Wireless (Default) |
| 43 | `Tense` | Tense SMS |
| 44 | `TruboSms` | Trubo SMS |
| 45 | `Twenty4BulkSms` | 24 Bulk SMS |
| 46 | `TwentyFourBulkSmsBD` | 24 Bulk SMS BD |
| 47 | `Viatech` | Viatech SMS |
| 48 | `WinText` | WinText SMS |
| 49 | `ZamanIt` | Zaman IT SMS |

### Provider Configuration Examples

#### SSL Wireless (Default)

```env
SMS_SSL_API_TOKEN=your_token
SMS_SSL_SID=your_sid
SMS_SSL_CSMS_ID=unique_csms_id
SMS_SSL_BATCH_CSMS_ID=batch_id_for_bulk
```

```php
use Xenon\LaravelBDSms\Provider\Ssl;

SMS::via(Ssl::class)->shoot('017XXXXXXXXX', 'Message');
```

#### BoomCast

```env
SMS_BOOM_CAST_URL=https://api.boomcast.com
SMS_BOOM_CAST_USERNAME=your_username
SMS_BOOM_CAST_PASSWORD=your_password
SMS_BOOM_CAST_MASKING=YourBrand
```

```php
use Xenon\LaravelBDSms\Provider\BoomCast;

SMS::via(BoomCast::class)->shoot('017XXXXXXXXX', 'Message');
```

#### Infobip (International)

```env
SMS_INFOBIP_BASE_URL=https://xxxxx.api.infobip.com
SMS_INFOBIP_USER=your_username
SMS_INFOBIP_PASSWORD=your_password
SMS_INFOBIP_FROM=YourBrand
```

```php
use Xenon\LaravelBDSms\Provider\Infobip;

SMS::via(Infobip::class)->shoot('017XXXXXXXXX', 'Message');
```

---

## 9. Custom Gateway

### 9.1 Overview

The `CustomGateway` provider allows integration with any SMS API not directly supported by the package.

### 9.2 Basic Usage

```php
use Xenon\LaravelBDSms\Sender;
use Xenon\LaravelBDSms\Provider\CustomGateway;

$sender = Sender::getInstance();

$sender->setProvider(CustomGateway::class)
       ->setUrl('https://api.yoursmsgateway.com/send')
       ->setMethod('post')
       ->setConfig([
           'api_key' => 'your_api_key',
           'sender_id' => 'YourBrand',
           'to' => '017XXXXXXXXX',
           'message' => 'Hello World!',
       ]);

$response = $sender->send();
```

### 9.3 With Custom Headers

```php
$sender->setProvider(CustomGateway::class)
       ->setUrl('https://api.yoursmsgateway.com/send')
       ->setMethod('post')
       ->setHeaders([
           'Authorization' => 'Bearer your_token',
           'Content-Type' => 'application/json',
           'X-Custom-Header' => 'value',
       ], true)  // true = JSON content type
       ->setConfig([
           'recipient' => '017XXXXXXXXX',
           'content' => 'Your message',
       ]);

$response = $sender->send();
```

### 9.4 GET Request Method

```php
$sender->setProvider(CustomGateway::class)
       ->setUrl('https://api.yoursmsgateway.com/send')
       ->setMethod('get')  // Use GET instead of POST
       ->setConfig([
           'apikey' => 'your_key',
           'number' => '017XXXXXXXXX',
           'text' => 'Message via GET',
       ]);

$response = $sender->send();
```

### 9.5 With Queue Support

```php
$sender->setProvider(CustomGateway::class)
       ->setUrl('https://api.yoursmsgateway.com/send')
       ->setMethod('post')
       ->setQueue(true)
       ->setQueueName('custom_sms')
       ->setTries(5)
       ->setBackoff(120)
       ->setConfig([
           'phone' => '017XXXXXXXXX',
           'msg' => 'Queued custom message',
       ]);

$response = $sender->send();
```

---

## 10. Architecture & Internals

### 10.1 Package Structure

```
packages/laravelbdsms/
├── src/
│   ├── Config/
│   │   └── sms.php                    # Configuration file
│   ├── Database/
│   │   └── migrations/
│   │       └── create_laravelbd_sms_table.php.stub
│   ├── Facades/
│   │   ├── SMS.php                    # Main SMS facade
│   │   ├── Logger.php                 # Logging facade
│   │   └── Request.php                # Request facade
│   ├── Handler/
│   │   ├── RenderException.php        # Provider/config exceptions
│   │   ├── ParameterException.php     # Parameter validation exceptions
│   │   └── ValidationException.php    # Data validation exceptions
│   ├── Helper/
│   │   └── Helper.php                 # Utility functions
│   ├── Job/
│   │   └── SendSmsJob.php             # Queue job for async sending
│   ├── Log/
│   │   └── Log.php                    # Logging manager
│   ├── Models/
│   │   └── LaravelBDSmsLog.php        # Eloquent model for logs
│   ├── Provider/
│   │   ├── ProviderRoadmap.php        # Provider interface
│   │   ├── AbstractProvider.php       # Base provider class
│   │   ├── Ssl.php                    # SSL Wireless provider
│   │   ├── CustomGateway.php          # Custom gateway provider
│   │   └── [48 more providers...]
│   ├── LaravelBDSmsServiceProvider.php # Service provider
│   ├── SMS.php                        # Main SMS handler class
│   ├── Request.php                    # HTTP request handler
│   └── Sender.php                     # Core sender (singleton)
├── composer.json
└── DOCUMENTATION.md
```

### 10.2 Class Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      FACADES LAYER                          │
├─────────────────────────────────────────────────────────────┤
│  SMS Facade ──────────► LaravelBDSms binding                │
│  Logger Facade ───────► LaravelBDSmsLogger binding          │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                      SERVICE LAYER                          │
├─────────────────────────────────────────────────────────────┤
│  SMS Class                                                  │
│  ├── via($provider): SMS                                    │
│  ├── shoot($number, $text): mixed                           │
│  └── shootWithQueue(...): mixed                             │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                      CORE LAYER                             │
├─────────────────────────────────────────────────────────────┤
│  Sender (Singleton)                                         │
│  ├── getInstance(): Sender                                  │
│  ├── setProvider($class): Sender                            │
│  ├── setConfig($config): Sender                             │
│  ├── setMobile($mobile): Sender                             │
│  ├── setMessage($message): Sender                           │
│  ├── setQueue($bool): Sender                                │
│  └── send(): mixed                                          │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                     PROVIDER LAYER                          │
├─────────────────────────────────────────────────────────────┤
│  ProviderRoadmap (Interface)                                │
│  ├── getData()                                              │
│  ├── setData()                                              │
│  ├── sendRequest()                                          │
│  ├── generateReport($result, $data)                         │
│  └── errorException()                                       │
│                                                             │
│  AbstractProvider (Abstract Class)                          │
│  ├── $senderObject                                          │
│  ├── generateReport(): JsonResponse                         │
│  └── toArray() / toJson()                                   │
│                                                             │
│  Concrete Providers: Ssl, BoomCast, Infobip, etc.           │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                     HTTP LAYER                              │
├─────────────────────────────────────────────────────────────┤
│  Request Class                                              │
│  ├── get($verify, $timeout): Response                       │
│  ├── post($verify, $timeout): Response                      │
│  ├── setHeaders($headers): Request                          │
│  └── setContentTypeJson($bool): Request                     │
│                                                             │
│  GuzzleHTTP Client ───────► SMS Gateway API                 │
└─────────────────────────────────────────────────────────────┘
```

### 10.3 SMS Sending Flow

```
┌────────────────────────────────────────────────────────────────┐
│                    COMPLETE SMS FLOW                           │
└────────────────────────────────────────────────────────────────┘

Step 1: Facade Call
═══════════════════
SMS::shoot('017XXX', 'Hello')
        │
        ▼
┌─────────────────────────────────────┐
│ SMS Facade                          │
│ getFacadeAccessor() → 'LaravelBDSms'│
└─────────────────────────────────────┘
        │
        ▼
Step 2: Container Resolution
════════════════════════════
┌─────────────────────────────────────┐
│ ServiceProvider.register()          │
│ - Get default_provider from config  │
│ - Create Sender::getInstance()      │
│ - Set provider and config           │
│ - Return new SMS($sender)           │
└─────────────────────────────────────┘
        │
        ▼
Step 3: SMS Class Processing
════════════════════════════
┌─────────────────────────────────────┐
│ SMS::shoot($number, $text)          │
│ - $sender->setMobile($number)       │
│ - $sender->setMessage($text)        │
│ - return $sender->send()            │
└─────────────────────────────────────┘
        │
        ▼
Step 4: Sender Validation
═════════════════════════
┌─────────────────────────────────────┐
│ Sender::send()                      │
│ - Validate config is array          │
│ - Validate mobile not empty         │
│ - Validate message not empty        │
│ - Call provider->errorException()   │
└─────────────────────────────────────┘
        │
        ▼
Step 5: Provider Processing
═══════════════════════════
┌─────────────────────────────────────┐
│ Provider::errorException()          │
│ - Validate required config keys     │
│ - Throw RenderException if missing  │
└─────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────┐
│ Provider::sendRequest()             │
│ - Build API query parameters        │
│ - Create Request object             │
│ - Set headers if needed             │
└─────────────────────────────────────┘
        │
        ▼
Step 6: HTTP Request
════════════════════
┌─────────────────────────────────────┐
│ Request::post() or Request::get()   │
│ - Check if queue enabled            │
│ - If queue: dispatch SendSmsJob     │
│ - If not: execute GuzzleHTTP        │
└─────────────────────────────────────┘
        │
   ┌────┴────┐
   ▼         ▼
Queue     Direct
   │         │
   ▼         ▼
Step 7a:  Step 7b:
Job       Guzzle
Dispatch  Request
   │         │
   ▼         ▼
(later)   API Response
   │         │
   └────┬────┘
        ▼
Step 8: Response Processing
═══════════════════════════
┌─────────────────────────────────────┐
│ Provider::generateReport()          │
│ - Format JSON response              │
│ - Include status, provider, time    │
│ - Include mobile and message        │
└─────────────────────────────────────┘
        │
        ▼
Step 9: Logging (Optional)
══════════════════════════
┌─────────────────────────────────────┐
│ Sender::logGenerate()               │
│ - Check if sms_log enabled          │
│ - Database: Logger::createLog()     │
│ - File: LaravelLog::info()          │
└─────────────────────────────────────┘
        │
        ▼
Step 10: Return Response
════════════════════════
┌─────────────────────────────────────┐
│ JSON Response returned to caller    │
│ {                                   │
│   "status": "response",             │
│   "response": "<api_response>",     │
│   "provider": "...",                │
│   "send_time": "...",               │
│   "mobile": "...",                  │
│   "message": "..."                  │
│ }                                   │
└─────────────────────────────────────┘
```

### 10.4 Singleton Pattern (Sender Class)

The `Sender` class uses the Singleton pattern for efficient resource management:

```php
class Sender
{
    private static $instance = null;

    public static function getInstance(): Sender
    {
        if (!File::exists(config_path('sms.php'))) {
            throw new RenderException("missing config/sms.php...");
        }

        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
```

**Benefits:**
- Single instance throughout request lifecycle
- Efficient memory usage
- Consistent state management
- Easy provider switching

---

## 11. Creating Custom Providers

### 11.1 Provider Interface

All providers must implement `ProviderRoadmap`:

```php
interface ProviderRoadmap
{
    public function getData();
    public function setData();
    public function sendRequest();
    public function generateReport($result, $data);
    public function errorException();
}
```

### 11.2 Abstract Provider Base

Extend `AbstractProvider` for common functionality:

```php
abstract class AbstractProvider implements ProviderRoadmap
{
    protected $senderObject;

    abstract public function sendRequest();
    abstract public function errorException();

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
}
```

### 11.3 Complete Provider Example

```php
<?php

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class MyNewProvider extends AbstractProvider
{
    /**
     * API endpoint URL
     */
    private string $apiEndpoint = 'https://api.mynewprovider.com/sms/send';

    /**
     * Constructor - receives Sender instance
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send the SMS request
     */
    public function sendRequest()
    {
        // Get data from Sender
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries = $this->senderObject->getTries();
        $backoff = $this->senderObject->getBackoff();

        // Build API query parameters
        $query = [
            'api_key' => $config['api_key'],
            'sender_id' => $config['sender_id'],
            'recipient' => $mobile,
            'message' => $text,
            'type' => $config['type'] ?? 'text',
        ];

        // Create Request object
        $requestObject = new Request(
            $this->apiEndpoint,
            $query,
            $queue,
            [],           // headers
            $queueName,
            $tries,
            $backoff
        );

        // Set headers if needed
        $requestObject->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->setContentTypeJson(true);

        // Execute request
        $response = $requestObject->post();

        // If queued, return immediately
        if ($queue) {
            return true;
        }

        // Process response
        $body = $response->getBody();
        $smsResult = $body->getContents();

        // Generate report
        $data['number'] = $mobile;
        $data['message'] = $text;

        return $this->generateReport($smsResult, $data)->getContent();
    }

    /**
     * Validate required configuration
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('api_key', $config)) {
            throw new RenderException('api_key is required for MyNewProvider');
        }

        if (!array_key_exists('sender_id', $config)) {
            throw new RenderException('sender_id is required for MyNewProvider');
        }

        if (empty($config['api_key'])) {
            throw new RenderException('api_key cannot be empty');
        }
    }
}
```

### 11.4 Register the Provider

Add to `config/sms.php`:

```php
use Xenon\LaravelBDSms\Provider\MyNewProvider;

'providers' => [
    // ... existing providers

    MyNewProvider::class => [
        'api_key' => env('SMS_MYNEW_API_KEY', ''),
        'sender_id' => env('SMS_MYNEW_SENDER_ID', ''),
        'type' => env('SMS_MYNEW_TYPE', 'text'),
    ],
]
```

### 11.5 Use the Provider

```php
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Provider\MyNewProvider;

// Via facade
SMS::via(MyNewProvider::class)->shoot('017XXXXXXXXX', 'Hello!');

// Or set as default in .env
SMS_DEFAULT_PROVIDER=Xenon\LaravelBDSms\Provider\MyNewProvider
```

---

## 12. Helper Functions

### 12.1 Helper Class Methods

The `Helper` class provides utility functions for phone number handling:

```php
use Xenon\LaravelBDSms\Helper\Helper;
```

### 12.2 Number Validation

```php
// Validate Bangladeshi mobile number
$isValid = Helper::numberValidation('017XXXXXXXXX');
// Returns: true

$isValid = Helper::numberValidation('12345');
// Returns: false

// Valid patterns:
// - 017XXXXXXXXX (11 digits starting with 01)
// - 01XXXXXXXXX (11 digits)
// - 8801XXXXXXXXX (13 digits with country code)
// - +8801XXXXXXXXX (with + prefix)
```

### 12.3 Number Formatting

```php
// Ensure 88 prefix for international format
$formatted = Helper::ensureNumberStartsWith88('017XXXXXXXXX');
// Returns: '88017XXXXXXXXX'

$formatted = Helper::ensureNumberStartsWith88('8801XXXXXXXXX');
// Returns: '8801XXXXXXXXX' (unchanged)

// Check and add prefix
$number = Helper::checkMobileNumberPrefixExistence('017XXXXXXXXX');
// Returns: '88017XXXXXXXXX'
```

### 12.4 Bulk Number Handling

```php
// Convert array to comma-separated string
$numbers = ['017XXXXXXXXX', '018XXXXXXXXX', '019XXXXXXXXX'];
$csv = Helper::getCommaSeperatedNumbers($numbers);
// Returns: '017XXXXXXXXX,018XXXXXXXXX,019XXXXXXXXX'
```

### 12.5 Provider Namespace

```php
// Ensure full provider namespace
$fullClass = Helper::ensurePrefix('Ssl');
// Returns: 'Xenon\LaravelBDSms\Provider\Ssl'

$fullClass = Helper::ensurePrefix('Xenon\LaravelBDSms\Provider\Ssl');
// Returns: 'Xenon\LaravelBDSms\Provider\Ssl' (unchanged)
```

---

## 13. Exception Handling

### 13.1 Exception Types

| Exception | Description | Common Causes |
|-----------|-------------|---------------|
| `RenderException` | Provider/configuration errors | Missing config keys, invalid provider |
| `ParameterException` | Parameter validation errors | Empty mobile/message, invalid config type |
| `ValidationException` | Data validation errors | Invalid data format |

### 13.2 RenderException Examples

```php
use Xenon\LaravelBDSms\Handler\RenderException;

// Missing config file
throw new RenderException("missing config/sms.php...");

// Provider not found
throw new RenderException("Sms Gateway Provider 'InvalidProvider' not found.");

// Missing config key
throw new RenderException("api_token key is absent in configuration");
```

### 13.3 ParameterException Examples

```php
use Xenon\LaravelBDSms\Handler\ParameterException;

// Invalid config type
throw new ParameterException('config must be an array');

// Empty mobile
throw new ParameterException('Mobile number should not be empty');

// Empty message
throw new ParameterException('Message text should not be empty');
```

### 13.4 Handling Exceptions

```php
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Handler\ParameterException;

try {
    $response = SMS::shoot('017XXXXXXXXX', 'Hello World!');
} catch (RenderException $e) {
    // Handle configuration errors
    Log::error('SMS Config Error: ' . $e->getMessage());
} catch (ParameterException $e) {
    // Handle parameter errors
    Log::error('SMS Parameter Error: ' . $e->getMessage());
} catch (\Exception $e) {
    // Handle other errors
    Log::error('SMS Error: ' . $e->getMessage());
}
```

### 13.5 Validation in Providers

Each provider validates its required configuration:

```php
// SSL provider validation
public function errorException()
{
    if (!array_key_exists('api_token', $this->senderObject->getConfig())) {
        throw new RenderException('api_token key is absent in configuration');
    }

    if (!array_key_exists('sid', $this->senderObject->getConfig())) {
        throw new RenderException('sid key is absent in configuration');
    }

    // Bulk SMS requires batch_csms_id
    if (is_array($this->senderObject->getMobile()) &&
        !array_key_exists('batch_csms_id', $this->senderObject->getConfig())) {
        throw new RenderException('batch_csms_id is required for bulk SMS');
    }
}
```

---

## 14. API Reference

### 14.1 SMS Facade

```php
use Xenon\LaravelBDSms\Facades\SMS;
```

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `shoot()` | `string $mobile`, `string $text` | `mixed` | Send SMS immediately |
| `via()` | `string $provider` | `SMS` | Switch provider |
| `shootWithQueue()` | `string $number`, `string $text`, `string $queueName = 'default'`, `int $tries = 3`, `int $backoff = 60` | `mixed` | Queue SMS |

### 14.2 Sender Class

```php
use Xenon\LaravelBDSms\Sender;
```

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `getInstance()` | - | `Sender` | Get singleton instance |
| `setProvider()` | `string $providerClass` | `Sender` | Set SMS provider |
| `setConfig()` | `array $config` | `Sender` | Set provider config |
| `setMobile()` | `string|array $mobile` | `Sender` | Set recipient(s) |
| `setMessage()` | `string $message` | `Sender` | Set message text |
| `setQueue()` | `bool $queue` | `Sender` | Enable/disable queue |
| `setQueueName()` | `string $name` | `Sender` | Set queue name |
| `setTries()` | `int $tries` | `Sender` | Set retry attempts |
| `setBackoff()` | `int $seconds` | `Sender` | Set retry delay |
| `setUrl()` | `string $url` | `Sender` | Set custom URL |
| `setMethod()` | `string $method` | `Sender` | Set HTTP method |
| `setHeaders()` | `array $headers`, `bool $json = true` | `Sender` | Set headers |
| `send()` | - | `mixed` | Send the SMS |
| `getProvider()` | - | `AbstractProvider` | Get current provider |
| `getConfig()` | - | `array` | Get current config |
| `getMobile()` | - | `string|array` | Get mobile number(s) |
| `getMessage()` | - | `string` | Get message text |
| `getQueue()` | - | `bool` | Get queue status |
| `getQueueName()` | - | `string` | Get queue name |
| `getTries()` | - | `int` | Get retry attempts |
| `getBackoff()` | - | `int` | Get backoff seconds |

### 14.3 Logger Facade

```php
use Xenon\LaravelBDSms\Facades\Logger;
```

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `createLog()` | `array $data` | `void` | Create log entry |
| `viewLastLog()` | - | `Model|null` | Get last log |
| `viewAllLog()` | - | `Collection` | Get all logs |
| `clearLog()` | - | `void` | Delete all logs |
| `logByProvider()` | `string $provider` | `Collection` | Logs by provider |
| `logByDefaultProvider()` | - | `Collection` | Default provider logs |
| `total()` | - | `int` | Total log count |
| `toArray()` | `object $log` | `array` | Convert to array |

### 14.4 Request Class

```php
use Xenon\LaravelBDSms\Request;
```

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `__construct()` | `$url`, `array $query`, `bool $queue = false`, `array $headers = []`, `string $queueName = 'default'`, `int $tries = 3`, `int $backoff = 60` | - | Constructor |
| `get()` | `bool $verify = false`, `float $timeout = 10.0` | `Response|void` | GET request |
| `post()` | `bool $verify = false`, `float $timeout = 20.0` | `Response|void` | POST request |
| `setHeaders()` | `array $headers` | `Request` | Set headers |
| `setContentTypeJson()` | `bool $json` | `Request` | Set JSON content type |
| `setFormParams()` | `array $params` | `Request` | Set form parameters |
| `getQueue()` | - | `bool` | Get queue status |

### 14.5 Helper Class

```php
use Xenon\LaravelBDSms\Helper\Helper;
```

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `ensurePrefix()` | `string $text` | `string` | Add provider namespace |
| `numberValidation()` | `string $number` | `bool` | Validate BD number |
| `getCommaSeperatedNumbers()` | `array $numbers` | `string` | Join with commas |
| `checkMobileNumberPrefixExistence()` | `string $mobile` | `string` | Ensure 88 prefix |
| `ensureNumberStartsWith88()` | `string $text` | `string` | Add 88 prefix |

---

## 15. Troubleshooting

### 15.1 Common Issues

#### Missing Config File

**Error:**
```
RenderException: missing config/sms.php
```

**Solution:**
```bash
php artisan vendor:publish --provider="Xenon\LaravelBDSms\LaravelBDSmsServiceProvider"
php artisan config:clear
```

#### Provider Not Found

**Error:**
```
RenderException: Sms Gateway Provider 'InvalidProvider' not found
```

**Solution:**
- Check provider class name spelling
- Ensure provider exists in `src/Provider/`
- Use full namespace: `Xenon\LaravelBDSms\Provider\Ssl`

#### Missing Configuration Keys

**Error:**
```
RenderException: api_token key is absent in configuration
```

**Solution:**
1. Check `.env` file for required variables
2. Verify `config/sms.php` has the provider configured
3. Run `php artisan config:clear`

#### Empty Mobile or Message

**Error:**
```
ParameterException: Mobile number should not be empty
ParameterException: Message text should not be empty
```

**Solution:**
- Validate input before calling `shoot()`
- Ensure variables are not null/empty

### 15.2 Queue Issues

#### Jobs Not Processing

**Solution:**
1. Ensure queue driver is configured in `.env`:
   ```env
   QUEUE_CONNECTION=database
   ```
2. Run migrations for jobs table:
   ```bash
   php artisan queue:table
   php artisan migrate
   ```
3. Start queue worker:
   ```bash
   php artisan queue:work
   ```

#### Jobs Failing Repeatedly

**Solution:**
1. Check failed jobs table:
   ```bash
   php artisan queue:failed
   ```
2. Increase timeout:
   ```bash
   php artisan queue:work --timeout=120
   ```
3. Check provider credentials

### 15.3 Logging Issues

#### Logs Not Saving

**Solution:**
1. Enable logging in `config/sms.php`:
   ```php
   'sms_log' => true,
   ```
2. Run migration:
   ```bash
   php artisan migrate
   ```
3. Check database connection

#### File Logs Not Appearing

**Solution:**
1. Set log driver:
   ```php
   'log_driver' => 'file',
   ```
2. Check `storage/logs/laravel.log` permissions
3. Ensure `storage/logs` is writable

### 15.4 HTTP Request Issues

#### SSL Certificate Errors

**Solution:**
In provider's `sendRequest()`, set verify to false:
```php
$response = $requestObject->post(false); // Disable SSL verification
```

#### Timeout Errors

**Solution:**
Increase timeout in request:
```php
$response = $requestObject->post(false, 30.0); // 30 second timeout
```

### 15.5 Debug Mode

Enable detailed error logging:

```php
try {
    $response = SMS::shoot('017XXXXXXXXX', 'Test');
    Log::info('SMS Response', ['response' => $response]);
} catch (\Exception $e) {
    Log::error('SMS Error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

---

## Appendix A: Environment Variables Reference

```env
# ===========================================
# LARAVELBDSMS CONFIGURATION
# ===========================================

# Default Provider
SMS_DEFAULT_PROVIDER=Xenon\LaravelBDSms\Provider\Ssl

# SSL Wireless
SMS_SSL_API_TOKEN=
SMS_SSL_SID=
SMS_SSL_CSMS_ID=
SMS_SSL_BATCH_CSMS_ID=

# BoomCast
SMS_BOOM_CAST_URL=
SMS_BOOM_CAST_USERNAME=
SMS_BOOM_CAST_PASSWORD=
SMS_BOOM_CAST_MASKING=

# Infobip
SMS_INFOBIP_BASE_URL=
SMS_INFOBIP_USER=
SMS_INFOBIP_PASSWORD=
SMS_INFOBIP_FROM=

# GreenWeb
SMS_GREEN_WEB_TOKEN=

# MimSms
SMS_MIM_SMS_SENDER_NAME=
SMS_MIM_SMS_API_KEY=
SMS_MIM_SMS_API_USERNAME=
SMS_MIM_SMS_API_TRANSACTION_TYPE=T
SMS_MIM_SMS_API_CAMPAIGN_ID=
SMS_MIM_SMS_API_CAMPAIGN_NAME=

# Onnorokom
SMS_ONNOROKOM_USERNAME=
SMS_ONNOROKOM_PASSWORD=
SMS_ONNOROKOM_TYPE=
SMS_ONNOROKOM_MASK=
SMS_ONNOROKOM_CAMPAIGN_NAME=

# ADN
SMS_ADN_SENDER_ID=
SMS_ADN_API_KEY=
SMS_ADN_API_SECRET=
SMS_ADN_API_REQUEST_TYPE=SINGLE_SMS
SMS_ADN_API_MESSAGE_TYPE=TEXT

# Banglalink
SMS_BANGLALINK_USERID=
SMS_BANGLALINK_PASSWD=
SMS_BANGLALINK_SENDER=

# Grameenphone
SMS_GRAMEENPHONE_USERNAME=
SMS_GRAMEENPHONE_PASSWORD=
SMS_GRAMEENPHONE_MESSAGETYPE=1

# Alpha SMS
SMS_ALPHA_SMS_API_KEY=

# Add more as needed...
```

---

## Appendix B: Database Schema

### lbs_log Table

```sql
CREATE TABLE `lbs_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `provider` TEXT NOT NULL,
    `request_json` TEXT NOT NULL,
    `response_json` TEXT NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Appendix C: Version History

| Version | Changes |
|---------|---------|
| v1.0.0 | Initial release |
| v1.0.20 | Added exception handling |
| v1.0.31 | Added SMS facade |
| v1.0.32 | Service provider improvements |
| v1.0.35 | Added logging system |
| v1.0.41.6-dev | Added queue support |
| v1.0.43.1-dev | Helper improvements |
| v1.0.46-dev | shootWithQueue method |
| v1.0.52.0-beta | Number formatting helpers |
| v1.0.55.0-beta | Custom headers support |

---

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Support

- **Author**: Ariful Islam
- **Email**: arif98741@gmail.com
- **GitHub**: https://github.com/arif98741

---

*Documentation generated for LaravelBDSMS package*
