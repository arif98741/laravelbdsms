<?php


namespace Xenon\LaravelBDSms\Provider;


interface ProviderRoadmap
{
    public function getData();

    public function setData();

    public function sendRequest();

    public function generateReport($result, $data);

    public function errorException();
}
