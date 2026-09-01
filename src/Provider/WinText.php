<?php

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Helper\Helper;

class WinText extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.wintextbd.com/SingleSms';


    /**
     * @return false|string
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function sendRequest()
    {
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $formParams = [
            "token" => $config['token'],
            "messagetype" => $config['messagetype'] ?? 1,
            "ismasking" => $config['ismasking'] ?? 'false',
            "masking" => $config['masking'] ?? 'null',
            "SMSText" => $text,
        ];

        if (!is_array($mobile)) {
            $formParams['mobileno'] = Helper::ensureNumberStartsWith88($mobile);
        } else {
            /*foreach ($mobile as $element) {
                $tempMobile[] = Helper::ensureNumberStartsWith88($element);
            }
            $formParams['mobileno'] = implode(',', $tempMobile);*/
        }

        //dd($this->apiEndpoint, $formParams);
        $requestObject = $this->makeRequest($this->apiEndpoint);
        $requestObject->setFormParams($formParams);
        return $this->respond($requestObject->post(false, 60));
    }

    /**
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('token', $config)) {
            throw new RenderException('token key is absent in configuration');
        }
    }
}
