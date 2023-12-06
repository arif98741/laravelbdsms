<?php
/*
|--------------------------------------------------------------------------
| Configuration For Multiple Gateways
|--------------------------------------------------------------------------
|
| This file is key value a pair of providers. Individual provider has different types of
| params and api request params. This file is generated after running command below from your terminal.
| php artisan vendor:publish --provider="Xenon\\LaravelBDSmsLog\\LaravelBDSmsServiceProvider"
| .Here All data ar dynamically coming from .env file.
| Be sure to confirm to select default provider during use SMS facade, otherwise you can manually send sms
| by selecting provider.
| Happy coding !!!!!!!!!!!!
|
*/

use Xenon\LaravelBDSms\Provider\Adn;
use Xenon\LaravelBDSms\Provider\AjuraTech;
use Xenon\LaravelBDSms\Provider\Alpha;
use Xenon\LaravelBDSms\Provider\Banglalink;
use Xenon\LaravelBDSms\Provider\BDBulkSms;
use Xenon\LaravelBDSms\Provider\BoomCast;
use Xenon\LaravelBDSms\Provider\Brilliant;
use Xenon\LaravelBDSms\Provider\BulkSmsBD;
use Xenon\LaravelBDSms\Provider\CustomGateway;
use Xenon\LaravelBDSms\Provider\DianaHost;
use Xenon\LaravelBDSms\Provider\DianaSms;
use Xenon\LaravelBDSms\Provider\DnsBd;
use Xenon\LaravelBDSms\Provider\ElitBuzz;
use Xenon\LaravelBDSms\Provider\Esms;
use Xenon\LaravelBDSms\Provider\Grameenphone;
use Xenon\LaravelBDSms\Provider\GreenWeb;
use Xenon\LaravelBDSms\Provider\Infobip;
use Xenon\LaravelBDSms\Provider\Lpeek;
use Xenon\LaravelBDSms\Provider\MDL;
use Xenon\LaravelBDSms\Provider\Metronet;
use Xenon\LaravelBDSms\Provider\MimSms;
use Xenon\LaravelBDSms\Provider\Mobireach;
use Xenon\LaravelBDSms\Provider\Mobishasra;
use Xenon\LaravelBDSms\Provider\Muthofun;
use Xenon\LaravelBDSms\Provider\NovocomBd;
use Xenon\LaravelBDSms\Provider\Onnorokom;
use Xenon\LaravelBDSms\Provider\QuickSms;
use Xenon\LaravelBDSms\Provider\RedmoItSms;
use Xenon\LaravelBDSms\Provider\SmartLabSms;
use Xenon\LaravelBDSms\Provider\Sms4BD;
use Xenon\LaravelBDSms\Provider\SmsinBD;
use Xenon\LaravelBDSms\Provider\SmsNet24;
use Xenon\LaravelBDSms\Provider\SmsNetBD;
use Xenon\LaravelBDSms\Provider\SMSNoc;
use Xenon\LaravelBDSms\Provider\SmsQ;
use Xenon\LaravelBDSms\Provider\Ssl;
use Xenon\LaravelBDSms\Provider\Tense;
use Xenon\LaravelBDSms\Provider\TruboSms;
use Xenon\LaravelBDSms\Provider\Twenty4BulkSms;
use Xenon\LaravelBDSms\Provider\TwentyFourSmsBD;
use Xenon\LaravelBDSms\Provider\Viatech;

return [
    /*
     *-----------------------------------------------------------------------------------------------
     | Sms Log will save sms request, provider name, and response in database table called `lbs_log`
     | You can change sms log to true/false according to your need. Default is set to true
     |---------------------------------------------------------------------------------------------
     */
    'sms_log' => false,

    /*
     *-----------------------------------------------------------------------------------------------
     | Default provider will be used during usage of facade( Xenon\LaravelBDSms\Facades\SMS )
     |---------------------------------------------------------------------------------------------
     */
    'default_provider' => env('SMS_DEFAULT_PROVIDER', Ssl::class),
    /*
     *---------------------------------------------------------------------------------------------------------------
     | Providers are companies or gateways those provide sms credentials as well as sell sms to customers.
     | This providers key store all the necessary credentials needed for using inside .env file; Be sure to use this
     | credentials in your .env file before sending sms. This will be used while you are sending sms using
     | facade(Xenon\LaravelBDSms\Facades\SMS)
     |-------------------------------------------------------------------------------------------------------------
     */
    'providers' => [
        Adn::class => [
            'senderid' => env('SMS_ADN_SENDER_ID', ''),
            'api_key' => env('SMS_ADN_API_KEY', ''),
            'api_secret' => env('SMS_ADN_API_SECRET', ''),
            'request_type' => env('SMS_ADN_API_REQUEST_TYPE', ''),
            'message_type' => env('SMS_ADN_API_MESSAGE_TYPE', ''),
        ],
        AjuraTech::class => [
            'apikey' => env('SMS_AjuraTechReveSms_API_KEY', ''),
            'secretkey' => env('SMS_AjuraTechReveSms_API_SECRET_KEY', ''),
            'callerID' => env('SMS_AjuraTechReveSms_CALLER_ID', ''),
        ],
        Alpha::class => [
            'api_key' => env('SMS_ALPHA_SMS_API_KEY'),
        ],
        Banglalink::class => [
            'userID' => env('SMS_BANGLALINK_USERID', ''),
            'passwd' => env('SMS_BANGLALINK_PASSWD', ''),
            'sender' => env('SMS_BANGLALINK_SENDER', ''),
        ],
        BDBulkSms::class => [
            'token' => env('SMS_BD_BULK_SMS_TOKEN', ''),
        ],
        BoomCast::class => [
            'url' => env('SMS_BOOM_CAST_URL', ''),
            'username' => env('SMS_BOOM_CAST_USERNAME', ''),
            'password' => env('SMS_BOOM_CAST_PASSWORD', ''),
            'masking' => env('SMS_BOOM_CAST_MASKING', ''),
        ],
        Brilliant::class => [
            'SenderId' => env('SMS_BRILLIANT_SENDER_ID', ''),
            'ApiKey' => env('SMS_BRILLIANT_API_KEY', ''),
            'ClientId' => env('SMS_BRILLIANT_CLIENT_ID', ''),
        ],
        BulkSmsBD::class => [
            'api_key' => env('SMS_BULK_SMS_BD_API_KEY', ''),
            'senderid' => env('SMS_BULK_SMS_BD_SENDERID', ''),
        ],
        CustomGateway::class => [

        ],
        DianaHost::class => [
            'senderid' => env('SMS_DIANA_HOST_SENDER_ID', ''),
            'api_key' => env('SMS_DIANA_HOST_API_KEY', ''),
            'type' => env('SMS_DIANA_HOST_TYPE', ''),
        ],
        DianaSms::class => [
            'SenderId' => env('SMS_DIANA_SMS_SENDER_ID', ''),
            'ApiKey' => env('SMS_DIANA_SMS_API_KEY', ''),
            'ClientId' => env('SMS_DIANA_SMS_CLIENT_ID', ''),
        ],
        DnsBd::class => [],
        ElitBuzz::class => [
            'url' => env('SMS_ELITBUZZ_URL', ''),
            'senderid' => env('SMS_ELITBUZZ_SENDER_ID', ''),
            'api_key' => env('SMS_ELITBUZZ_API_KEY', ''),
        ],
        Esms::class => [
            'sender_id' => env('SMS_ESMS_SENDER_ID', ''),
            'api_token' => env('SMS_ESMS_API_TOKEN', ''),
        ],
        Grameenphone::class => [
            'username' => env('SMS_GRAMEENPHONE_USERNAME', ''),
            'password' => env('SMS_GRAMEENPHONE_PASSWORD', ''),
            'messagetype' => env('SMS_GRAMEENPHONE_MESSAGETYPE', 1),
        ],
        GreenWeb::class => [
            'token' => env('SMS_GREEN_WEB_TOKEN', ''),
        ],
        Infobip::class => [
            'base_url' => env('SMS_INFOBIP_BASE_URL', ''),
            'user' => env('SMS_INFOBIP_USER', ''),
            'password' => env('SMS_INFOBIP_PASSWORD', ''),
            'from' => env('SMS_INFOBIP_FROM', ''),
        ],
        Lpeek::class => [
            'acode' => env('SMS_LPEEK_ACODE', ''),
            'apiKey' => env('SMS_LPEEK_APIKEY', ''),
            'requestID' => env('SMS_LPEEK_REQUESTID', ''),
            'masking' => env('SMS_LPEEK_MASKING', ''),
            'is_unicode' => env('SMS_LPEEK_IS_UNICODE', '0'),
            'transactionType' => env('SMS_LPEEK_TRANSACTIONTYPE', 'T'),
        ],
        MDL::class => [
            'senderid' => env('SMS_MDL_SENDER_ID', ''),
            'api_key' => env('SMS_MDL_API_KEY', ''),
            'type' => env('SMS_MDL_TYPE', ''),
        ],
        Metronet::class => [
            'api_key' => env('SMS_METRONET_API_KEY', ''),
            'mask' => env('SMS_METRONET_MASK', ''),
        ],
        MimSms::class => [
            'senderid' => env('SMS_MIM_SMS_SENDER_ID', ''),
            'api_key' => env('SMS_MIM_SMS_API_KEY', ''),
            'type' => env('SMS_MIM_SMS_TYPE', ''),
        ],
        Mobireach::class => [
            'Username' => env('SMS_MOBIREACH_USERNAME', ''),
            'Password' => env('SMS_MOBIREACH_PASSWORD', ''),
            'From' => env('SMS_MOBIREACH_FROM', ''),
        ],
        Mobishasra::class => [
            'user' => env('SMS_MOBISHASTRA_USERNAME', ''),
            'pwd' => env('SMS_MOBISHASTRA_PASSWORD', ''),
            'senderid' => env('SMS_MOBISHASTRA_SENDER_ID', ''),
        ],
        Muthofun::class => [
            'api_key' => env('SMS_MUTHOFUN_API_KEY'),
            'sender_id' => env('SMS_MUTHOFUN_SENDER_ID'),
        ],
        NovocomBd::class => [
            'SenderId' => env('SMS_NOVOCOMBD_SENDER_ID', ''),
            'ApiKey' => env('SMS_NOVOCOMBD_API_KEY', ''),
            'ClientId' => env('SMS_NOVOCOMBD_CLIENT_ID', ''),
        ],
        Onnorokom::class => [
            'userName' => env('SMS_ONNOROKOM_USERNAME', ''),
            'userPassword' => env('SMS_ONNOROKOM_PASSWORD', ''),
            'type' => env('SMS_ONNOROKOM_TYPE', ''),
            'maskName' => env('SMS_ONNOROKOM_MASK', ''),
            'campaignName' => env('SMS_ONNOROKOM_CAMPAIGN_NAME', ''),
        ],
        QuickSms::class => [
            'api_key' => env('SMS_QUICKSMS_API_KEY'),
            'senderid' => env('SMS_QUICKSMS_SENDER_ID'),
            'type' => env('SMS_QUICKSMS_SENDER_ID'),
            'scheduledDateTime' => env('SMS_QUICKSMS_SCHEDULED_DATE_TIME'),
        ],
        RedmoItSms::class => [
            'sender_id' => env('SMS_REDMOIT_SENDER_ID', ''),
            'api_token' => env('SMS_REDMOIT_API_TOKEN', ''),
            'type' => env('SMS_REDMOIT_TYPE', 'string'),
        ],
        SmartLabSms::class => [
            'user' => env('SMS_SMARTLAB_USER', ''),
            'password' => env('SMS_SMARTLAB_PASSWORD', ''),
            'sender' => env('SMS_SMARTLAB_SENDER', ''),
        ],
        Sms4BD::class => [
            'publickey' => env('SMS_SMS4BD_PUBLIC_KEY', ''),
            'privatekey' => env('SMS_SMS4BD_PRIVATE_KEY', ''),
            'type' => env('SMS_SMS4BD_TYPE', ''),
            'sender' => env('SMS_SMS4BD_SENDER', ''),
            'delay' => env('SMS_SMS4BD_DELAY', ''),
        ],
        SmsNet24::class => [
            'user_id' => env('SMS_SMSNET24_USER_ID', ''),
            'user_password' => env('SMS_SMSNET24_USER_PASSWORD', ''),
            'route_id' => env('SMS_SMSNET24_ROUTE_ID', ''),
            'sms_type_id' => env('SMS_SMSNET24_SMS_TYPE_ID', ''),
        ],
        SMSNoc::class => [
            'sender_id' => env('SMSNOC_SENDER_ID', ''),
            'bearer_token' => env('SMSNOC_BEARER_TOKEN', ''),

        ],
        SmsinBD::class => [
            'api_token' => env('SMSINBD_API_TOKEN', ''),
            'senderid' => env('SMSINBD_SENDERID', ''),
        ],
        SmsNetBD::class => [
            'api_key' => env('SMS_NET_BD_API_KEY'),
        ],
        SmsQ::class => [
            'sender_id' => env('SMS_SMSQ_SENDER_ID', ''),
            'api_key' => env('SMS_SMSQ_API_KEY', ''),
            'client_id' => env('SMS_SMSQ_CLIENT_ID', ''),
        ],
        Ssl::class => [
            'api_token' => env('SMS_SSL_API_TOKEN', ''),
            'sid' => env('SMS_SSL_SID', ''),
            'csms_id' => env('SMS_SSL_CSMS_ID', ''),
        ],
        Tense::class => [
            'user' => env('SMS_TENSE_USER', ''),
            'password' => env('SMS_TENSE_PASSWORD', ''),
            'campaign' => env('SMS_TENSE_CAMPAIGN', ''),
            'masking' => env('SMS_TENSE_MASKING', ''),
        ],
        TruboSms::class => [
            'sender_id' => env('SMS_TRUBOSMS_SENDER_ID', ''),
            'api_token' => env('SMS_TRUBOSMS_API_TOKEN', ''),
            'type' => env('SMS_TRUBOSMS_TYPE', 'string'),
        ],
        TwentyFourSmsBD::class => [
            'apiKey' => env('SMS_TWENTYFOURSMSBD_APIKEY', ''),
            'sender_id' => env('SMS_TWENTYFOURSMSBD_SENDER_ID', ''),
        ],
        Viatech::class => [
            'api_key' => env('SMS_VIATECH_API_KEY', ''),
            'mask' => env('SMS_VIATECH_MASK', ''),
        ],
        Twenty4BulkSms::class => [
            'sender_id' => env('SMS_TWENTYFOUR_BULKSMS_SENDER_ID', ''),
            'user_email' => env('SMS_TWENTYFOUR_BULKSMS_USER_EMAIL', ''),
            'api_key' => env('SMS_TWENTYFOUR_BULKSMS_APP_KEY', ''),
        ],
    ]
];

