<?php


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
        $numbers = "017xxxxxxxx,018xxxxxxxx"; //Recipient Phone Number multiple number must be separated by comma
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
