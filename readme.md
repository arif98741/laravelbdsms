Xenon\LaravelBDSms is a sms gateway package for sending text message to Bangladeshi mobile numbers using several gateways for <strong>Laravel</strong>. You should use <strong>composer 2</strong> for latest updates of this package.

### Installation
# step
```
composer require xenon/laravelbdsms
```

### Sample Code DianaHost

<pre>
use Xenon\LaravelBDSms\Provider\DianaHost;
use Xenon\LaravelBDSms\Sender;


$sender = Sender::getInstance();
$sender->setProvider(DianaHost::class); 
$sender->setMobile('017XXYYZZAA');
$sender->setMessage('helloooooooo boss!');
$sender->setConfig(
   [
       'api_key' => 'your_api_goes_here',
       'type' => 'text',
       'senderid' => 'sender_id'
   ]
);
$status = $sender->send();
</pre>


### Sample Code SSLCommerz

<pre>
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Sender;

$sender = Sender::getInstance();
$sender->setProvider(Ssl::class); 
$sender->setMobile('017XXYYZZAA');
$sender->setMessage('helloooooooo boss!');
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

#### Currently Supported SMS Gateways
* BDBulkSMS
* BulkSMSBD
* Dianahost
* MDLSMS
* Metronet
* OnnoRokomSMS
* SSLSms

We are continuously working in this open source library for adding more Bangladeshi sms gateway. If you feel something is missing then make a issue regarding that.
If you want to contribute in this library, then you are highly welcome to do that....

For clear documentation read this blog in  [Medium!](https://send-sms-using-laravelbdsms.medium.com/laravel-sms-gateway-package-for-bangladesh-e70af99f2060)
