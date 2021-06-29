Xenon\LaravelBDSms is a sms gateway package for sending text message to Bangladeshi mobile numbers using several gateways using <strong>Laravel</strong>

### Installation
# step
```
composer require xenon/laravelbdsms
```

### Sample Code

<pre>
use Xenon\LaravelBDSms\Provider\DianaHost;
use Xenon\LaravelBDSms\Sender;


$sender = Sender::getInstance();
$sender->setProvider(DianaHost::class); //this is demo for Dianahost
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
* Dianahost
* MDLSMS
* Metronet
* OnnoRokomSMS
* SSLSms

We are continuously working in this open source library for adding more Bangladeshi sms gateway. If you feel something is missing then make a issue regarding that.
If you want to contribute in this library, then you are highly welcome to do that....

