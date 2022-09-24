Xenon\LaravelBDSms is a sms gateway package for sending text message to Bangladeshi mobile numbers using several
gateways for <strong>Laravel Framework</strong>.

<p><img src="https://img.shields.io/github/issues/arif98741/laravelbdsms">
<img src="https://img.shields.io/github/forks/arif98741/laravelbdsms">
<img src="https://img.shields.io/github/stars/arif98741/laravelbdsms">
   <img src="https://img.shields.io/github/license/arif98741/laravelbdsms">
</p>

<!-- TOC -->
* [Installation](#installation)
  * [Step 1:](#step-1-)
  * [Step 2:](#step-2-)
  * [Step 3:](#step-3-)
  * [Step 4:](#step-4-)
  * [Usage](#usage)
    * [Simply use the facade](#simply-use-the-facade)
    * [Or, with facade alias](#or-with-facade-alias)
    * [Or, if you need to change the default provider on the fly](#or-if-you-need-to-change-the-default-provider-on-the-fly)
    * [Or, if you want to send message with queue. This queue will be added in your jobs. Then this will be sent as soon as job is run.](#or-if-you-want-to-send-message-with-queue-this-queue-will-be-added-in-your-jobs-then-this-will-be-sent-as-soon-as-job-is-run)

    * [Sample Code SSLCommerz](#sample-code-sslcommerz)
    * [Sample Code MimSMS](#sample-code-mimsms)
    * [Demo Response Using SSL](#demo-response-using-ssl)
    * [Currently Supported SMS Gateways](#currently-supported-sms-gateways)
<!-- TOC -->


# Installation

## Step 1:

```
composer require xenon/laravelbdsms
```

## Step 2:

Then, publish the package

```
php artisan vendor:publish --provider=Xenon\LaravelBDSms\LaravelBDSmsServiceProvider
```

## Step 3:

Select Vendor From Console <br>
<img src="https://raw.githubusercontent.com/arif98741/laravelbdsms/master/img/installation.png" style="width: 60%; height: 60%">

## Step 4:

```
php artisan config:cache && php artisan migrate
```

[//]: # (This will create a `sms.php` in the `config/` directory and also table in your database. Set your desired provider as `default_provider` and fill up the)

[//]: # (necessary environment variable of that provider.)

## Usage

### Simply use the facade
<pre>
use Xenon\LaravelBDSms\Facades\SMS;

SMS::shoot('017XXYYZZAA', 'helloooooooo boss!');

SMS::shoot(['017XXYYZZAA','018XXYYZZAA'], 'helloooooooo boss!'); //for Ssl Sms Gateway Only
</pre>

### Or, with facade alias
<pre>
use LaravelBDSms;

LaravelBDSms::shoot('017XXYYZZAA', 'helloooooooo boss!');
</pre>

### Or, if you need to change the default provider on the fly
<pre>
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Provider\Ssl;

$response = SMS::via(Ssl::class)->shoot('017XXYYZZAA', 'helloooooooo boss!');
</pre>
That should do it.


### Or, if you want to send message with queue. This queue will be added in your jobs. Message be sent as soon as job is run.
<pre>
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Provider\Ssl;

SMS::shootWithQueue("01XXXXXXXXX",'test sms');
SMS::via(Ssl::class)->shootWithQueue("01XXXXXXXXX",'test sms');
</pre>
That should do it.

#

Otherwise, if you want more control, you can use the underlying sender object. This will not touch any laravel facade or
service provider.


### Sample Code SSLCommerz

<pre>
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Sender;

$sender = Sender::getInstance();
$sender->setProvider(Ssl::class); 
$sender->setMobile('017XXYYZZAA');
//$sender->setMobile(['017XXYYZZAA','018XXYYZZAA']);
$sender->setMessage('helloooooooo boss!');
$sender->setQueue(true); //if you want to sent sms from queue
$sender->setConfig(
   [
       'api_token' => 'api token goes here',
       'sid' => 'text',
       'csms_id' => 'sender_id'
   ]
);
$status = $sender->send();
</pre>

### Sample Code MimSMS

<pre>
use Xenon\LaravelBDSms\Provider\MimSms;
use Xenon\LaravelBDSms\Sender;

$sender = Sender::getInstance();
$sender->setProvider(MimSms::class);
$sender->setMobile('017XXYYZZAA');
$sender->setMessage('This is test message');
$sender->setQueue(true); //if you want to sent sms from queue
$sender->setConfig(
   [
       'api_key' => 'api_key_goes_here',
       'type' => 'text',
       'senderid' => 'approved_send_id',
   ]
);

$status = $sender->send();
</pre>



### Demo Response Using SSL

<pre>
array:6 [▼
  "status" => "response"
  "response" => "{"status":"FAILED","status_code":4003,"error_message":"IP Blacklisted"}"
  "provider" => "Xenon\LaravelBDSms\Provider\Ssl"
  "send_time" => "2021-07-06 08:03:23"
  "mobile" => "017XXYYZZAA"
  "message" => "helloooooooo boss!"
]
</pre>

### Currently Supported SMS Gateways

* AjuraTech
* Adn
* Banglalink
* BDBulkSMS
* BoomCast
* BulkSMSBD
* Dianahost
* Dianasms
* Esms
* ElitBuzz
* Infobip
* MDLSMS
* Metronet
* Mimsms
* Mobireach
* NovocomBD
* OnnoRokomSMS
* SSLSms
* Tense
* TwentyFourSmsBD
* Viatech

We are continuously working in this open source library for adding more Bangladeshi sms gateway. If you feel something
is missing then make a issue regarding that. If you want to contribute in this library, then you are highly welcome to
do that....

For clear documentation read this blog
in  [Medium!](https://send-sms-using-laravelbdsms.medium.com/laravel-sms-gateway-package-for-bangladesh-e70af99f2060)

Special thanks to <br>
[tusharkhan](https://github.com/tusharkhan) <br>
[tusher9352](https://github.com/tusher9352)

