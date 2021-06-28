<?php


namespace Xenon\LaravelBDSms\Provider;



abstract class AbstractProvider implements ProviderRoadmap
{
    protected $senderObject;

    public function getData()
    {
        // TODO: Implement setData() method.

    }

    public function setData()
    {
        // TODO: Implement setData() method.
    }

    abstract public function sendRequest();

    /**
     * @param $result
     * @param $data
     * @return mixed
     */
    abstract public function generateReport($result, $data);

    /**
     * @return mixed
     */
    abstract public function errorException();

    /**
     * Return Report As Array
     */
    public function toArray(): array
    {
        return [

        ];
    }

    /**
     * Return Report As Json
     */
    public function toJson()
    {
        return json_encode();
    }
}
