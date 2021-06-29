<?php
/*
 *  Last Modified: 6/28/21, 11:18 PM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;


use Xenon\Sender;

class Sms4BD implements ProviderRoadmap
{
    private $senderObject;

    /**
     * Sms4BD constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    public function getData()
    {
        // TODO: Implement getData() method.
    }

    public function setData()
    {
        // TODO: Implement setData() method.
    }

    public function sendRequest()
    {
        // TODO: Implement sendRequest() method.
    }

    /**
     * @param $result
     * @param $data
     * @return void
     */
    public function generateReport($result, $data)
    {
        // TODO: Implement generateReport() method.
    }
}
