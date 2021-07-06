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


class Alpha implements ProviderRoadmap
{

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
        $username = "YOUR_API_USERNAME";
        $hash = "YOUR_API_HASH_TOKEN";
        $numbers = "017xxxxxxxx,018xxxxxxxx";
        $message = "Simple text message.";


        $params = array('app'=>'ws', 'u'=>$username, 'h'=>$hash, 'op'=>'pv', 'unicode'=>'1','to'=>$numbers, 'msg'=>$message);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://alphasms.biz/index.php?".http_build_query($params, "", "&"));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "Accept:application/json"));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($ch);
        curl_close ($ch);
    }


    /**
     * @param $result
     * @param $data
     * @return mixed
     */
    public function generateReport($result, $data)
    {
        // TODO: Implement generateReport() method.
    }

    /**
     * @return mixed
     */
    public function errorException()
    {
        // TODO: Implement errorException() method.
    }
}
