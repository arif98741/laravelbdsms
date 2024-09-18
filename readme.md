Xenon\LaravelBDSms is a sms gateway package for sending text message to Bangladeshi mobile numbers using several
gateways for <strong>Laravel Framework</strong>. You can watch installation process [from youtube](https://youtu.be/i2wjLNoIvIo).

<p><img src="https://img.shields.io/github/issues/arif98741/laravelbdsms">
<img src="https://img.shields.io/github/forks/arif98741/laravelbdsms">
<img src="https://img.shields.io/github/stars/arif98741/laravelbdsms">
   <img src="https://img.shields.io/github/license/arif98741/laravelbdsms">
</p>

<!-- TOC -->
* [Installation](#installation)
  * [Step 1:](#step-1)
  * [Step 2:](#step-2)
  * [Step 3:](#step-3)
  * [Step 4:](#step-4)
  * [Usage](#usage)
    * [Simply use the facade](#simply-use-the-facade)
    * [Or, with facade alias](#or-with-facade-alias)
    * [Or, if you need to change the default provider on the fly](#or-if-you-need-to-change-the-default-provider-on-the-fly)
    * [Or, you can send message with queue. This queue will be added in your jobs table. Message will be sent as soon as job is run.](#or-you-can-send-message-with-queue-this-queue-will-be-added-in-your-jobs-table-message-will-be-sent-as-soon-as-job-is-run-)
* [Log Generate](#log-generate)

* [Sample Code](#sample-code)
  * [SSLCommerz](#sslcommerz)
  * [MimSms](#mimsms)
  * [Sms Send Using Custom Gateway](#sms-send-using-custom-gateway)
* [Currently Supported Sms Gateways](#currently-supported-sms-gateways)
    * [Stargazers](#stargazers)
    * [Forkers](#forkers)
    * [Contributors](#contributors)
<!-- TOC -->


# Installation

## Step 1:

```
composer require xenon/laravelbdsms
```

## Step 2:

Publish the package using command

```
php artisan vendor:publish --provider=Xenon\LaravelBDSms\LaravelBDSmsServiceProvider --tag="migrations"
php artisan vendor:publish --provider=Xenon\LaravelBDSms\LaravelBDSmsServiceProvider --tag="config"
php artisan migrate
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
`Note: For sending message using facade you must have to set .env credentials and set default provider; Find .env credentials for different providers from inside config/sms.php)`
<pre>
use Xenon\LaravelBDSms\Facades\SMS;

SMS::shoot('017XXYYZZAA', 'helloooooooo boss!');
SMS::shoot(['017XXYYZZAA','018XXYYZZAA'], 'helloooooooo boss!'); 
</pre>

### Or, with facade alias
<pre>
use LaravelBDSms, SMS;

LaravelBDSms::shoot('017XXYYZZAA', 'helloooooooo boss!');
SMS::shoot('017XXYYZZAA', 'helloooooooo boss!');
</pre>

### Or, if you need to change the default provider on the fly
<pre>
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Provider\Ssl;

$response = SMS::via(Ssl::class)->shoot('017XXYYZZAA', 'helloooooooo boss!');
</pre>


### Or, you can send message with queue. This queue will be added in your jobs table. Message will be sent as soon as job is run. 
Make sure you have **jobs** table and other jobs related functionalities enabled
<pre>
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Provider\Ssl;

SMS::shootWithQueue("01XXXXXXXXX",'test sms');
SMS::via(Ssl::class)->shootWithQueue("01XXXXXXXXX",'test sms');
</pre>

# Log Generate
You can generate log for every sms api request and save in database or file. For doing this. Follow below points
1. Laravelbdsms stores log in two drivers(`database, file`). `database` is default. You can change it from _config/sms.php_
2. Find and make true `'sms_log' => true,`
3. Be confirm you have completed **step-2** and **step-3**
4. For `database` driver
   1. Change log driver to `log_driver =>'database'` from `config/sms.php`
   2. Run command `php artisan migrate`. This will create `lbs_log` table in your database
5. For `file` driver
    1. Change log driver to `log_driver =>'file'` from `config/sms.php`

Otherwise, if you want more control, you can use the underlying sender object. This will not touch any laravel facade or
service provider.

# Sample Code
## SSLCommerz
<pre>
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Sender;

$sender = Sender::getInstance();
$sender->setProvider(Ssl::class); //change this provider class according to need
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

----------Demo Response Using SSL-------------
array:6 [▼
  "status" => "response"
  "response" => "{"status":"FAILED","status_code":4003,"error_message":"IP Blacklisted"}"
  "provider" => "Xenon\LaravelBDSms\Provider\Ssl"
  "send_time" => "2021-07-06 08:03:23"
  "mobile" => "017XXYYZZAA"
  "message" => "helloooooooo boss!"
]
--------------------------------------------------
</pre>


## Sms Send Using Custom Gateway
We have tried to added most of the gateways of Bangladesh in this package as much as possible. But still if you don't find your expected gateway in this list, then use Custom Gateway using following code snippet.
<pre>
use Xenon\LaravelBDSms\Provider\CustomGateway;
use Xenon\LaravelBDSms\Sender;

$sender = Sender::getInstance();
$sender->setProvider(CustomGateway::class);
$sender->setUrl('https://your_cusom_gateway_provider_url_here')
        ->setMethod('post')
        ->setHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        ], false);
$sender->setMobile('017XXYYZZAA');
$sender->setMessage('text message goes here');
$sender->setQueue(false);
//use required parameters based on your sms gateway. This will be changed according to need
$sender->setConfig(
    [
        'MsgType' => 'TEXT',
        'masking' => 'sample',
        'userName' => 'test_user',
        'message' => 'test message',
        'receiver' => '017xxxxxxxxxx',
    ]
);
echo $status = $sender->send();
</pre>

# Currently Supported Sms Gateways

| Provider            | Credentials  Required <br>    For Sending SMS                     | Support Multiple | Status         | Comment                                                  | Contact |
|---------------------|-------------------------------------------------------------------|------------------|----------------|----------------------------------------------------------|---------|
| AjuraTech           | apikey, secretkey , callerID                                      | -                | Done           | -                                                        | -       |
| Adn                 | api_key, type, senderid                                           | -                | Done           | -                                                        | -       |
| Alpha               | api_key                                                           | Yes              | Done           | -                                                        | -       |
| Banglalink          | userID, passwd , sender                                           | -                | Done           | -                                                        | -       |
| BDBulkSMS           | token                                                             | -                | Done           | -                                                        | -       |
| BoomCast            | masking  , userName ,   password                                  | -                | Done           | -                                                        | -       |
| BulksmsBD           | api_key,senderid                                                  | -                | Done           | -                                                        | -       |
| CustomGateway       | provide necessary token/api_key/others based on requirements      | -                | Done           | Be careful using this and test based on several scenario | -       |
| DhorolaSms          | apikey, sender                                                    | -                | Done           | -                                                        | -       |
| DianaHost           | api_key, type, senderid                                           | -                | Done           | -                                                        | -       |
| DianaSMS            | ApiKey, ClientId, SenderId                                        | -                | Done           | -                                                        | -       |
| DurjoySoft          | ApiKey, SenderID                                                  | -                | Done           | -                                                        | -       |
| ElitBuzz            | api_key, type, senderid                                           | -                | Done           | not tested yet in live                                   | -       |
| Esms                | api_token, sender_id                                              | -                | Done           | -                                                        | -       |
| Grameenphone        | username, password, messagetype                                   | -                | Done           | not tested yet in live                                   | -       |
| Infobip             | user, password                                                    | -                | Done           | not tested yet in live                                   | -       |
| Lpeek               | acode, apiKey, requestID, masking                                 | -                | Done           | -                                                        | -       |
| MDL                 | api_key, type, senderid                                           | -                | Done           | not tested yet in live                                   | -       |
| Metronet            | api_key, mask                                                     |                  | Done           | -                                                        | -       |
| MimSms              | api_key, type, senderid                                           | -                | Done           | -                                                        | -       |
| Mobireach           | Username,Password, From                                           | -                | Done           | -                                                        | -       |
| Muthofun            | sender_id                                                         | Yes              | Done           | -                                                        | -       |
| NovocomBD           | ApiKey , ClientId   , SenderId                                    | -                | Done           | -                                                        | -       |
| OnnoRokomSMS        | userName, userPassword, type, maskName, campaignName              | -                | Done           | not tested yet in live                                   | -       |
| QuickSms            | api_key, senderid, type,scheduledDateTime                         | -                | Done           | not tested yet in live                                   | -       |
| RedmoITSms          | api_token, sender_id                                              | -                | Support closed | -                                                        |
| RedmoITSms          | api_token, sender_id                                              | -                | Support closed | -                                                        |
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
| Twenty4BulkSms      | api_key, sender_id,user_email                                     | -                | Done           | -                                                        | -       |
| TwentyFourBulkSmsBD | customer_id, api_key                                              | -                | Done           | -                                                        | -       |
| Trubosms            | api_token, sender_id                                              | -                | Done           | -                                                        | -       |
| Viatech             | api_key, mask                                                     | -                | Done           | -                                                        | -       |
| WinText             | token, messagetype, ismasking, masking                            | -                | Done           | -                                                        | -       |
| ZamanIT             | api_key, senderid,type                                            | -                | Done           | -                                                        | -       |




### Stargazers
[![Stargazers repo roster for @arif98741/laravelbdsms](https://reporoster.com/stars/arif98741/laravelbdsms)](https://github.com/arif98741/laravelbdsms/stargazers)

### Forkers
[![Forkers repo roster for @arif98741/laravelbdsms](https://reporoster.com/forks/arif98741/laravelbdsms)](https://github.com/arif98741/laravelbdsms/network/members)

### Contributors
<a href="https://github.com/arif98741/laravelbdsms/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=arif98741/laravelbdsms" />
</a>

<br> 
We are continuously working in this open source library for adding more Bangladeshi sms gateway. If you feel something
is missing then make a issue regarding that. If you want to contribute in this library, then you are highly welcome to
do that....

For clear documentation read this blog
in  [Medium!](https://send-sms-using-laravelbdsms.medium.com/laravel-sms-gateway-package-for-bangladesh-e70af99f2060)
and also you can download several sms providers documentations as pdf from [this link!](https://github.com/arif98741/laravelbdsms/archive/refs/heads/doc.zip)


Special thanks to <br>
[tusharkhan](https://github.com/tusharkhan) <br>
[tusher9352](https://github.com/tusher9352)

