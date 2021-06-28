Xenon\LaravelBDSms is a sms gateway package for sending text message to Bangladeshi mobile numbers using several gateways using <strong>Laravel</strong>

### Installation
#step 1
```
composer require xenon/laravelbdsms
```

### Sample Code

<pre>
use Xenon\LaravelBDSms\Provider\DianaHost;
use Xenon\LaravelBDSms\Sender;


$sender = Sender::getInstance();
$sender->selectProvider(DianaHost::class); //this is demo for Dianahost
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


#### Currently Supported SMS Gateways
* BDBulkSMS
* BulkSMSBD
* MDLSMS
* OnnoRokomSMS
* SSLSms

We are continuously working in this open source library for adding more Bangladeshi sms gateway. If you fee something is missing then make a issue regarding that.
If you want to contribute in this library, then you are highly welcome to do that.

