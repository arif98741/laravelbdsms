<?php


namespace Xenon\LaravelBDSms\Provider;


use Xenon\Handler\XenonException;
use Xenon\Sender;

class GreenWeb extends AbstractProvider
{
    /**
     * Green web SMS constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Request To Green Web Server
     */
    public function sendRequest()
    {
        $to = $this->senderObject->getMobile();
        $config = $this->senderObject->getConfig();
        $token = $config['token'];
        $message = $this->senderObject->getMessage();

        $url = "https://api.greenweb.com.bd/api.php?json";

        $data = array(
            'to' => "$to",
            'message' => "$message",
            'token' => "$token"
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $smsResult = curl_exec($ch);
        if ($smsResult == false) {
            $smsResult = curl_error($ch);
        }
        curl_close($ch);
        $data['number'] = $to;
        return $this->generateReport($smsResult, $data);
    }

    /**
     * @param $result
     * @param $data
     * @return array
     */
    public function generateReport($result, $data): array
    {
        return [
            'status' => 'response',
            'response' => $result,
            'provider' => self::class,
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ];
    }   // TODO: Implement generateReport() method.


    /**
     * @throws XenonException
     */
    public function errorException()
    {
        if (!array_key_exists('to', $this->senderObject->getConfig()))
            throw new XenonException('to key is absent in configuration');
        if (!array_key_exists('token', $this->senderObject->getConfig()))
            throw new XenonException('token key is absent in configuration');

    }
}
