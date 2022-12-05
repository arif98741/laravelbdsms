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


use Illuminate\Http\JsonResponse;

abstract class AbstractProvider implements ProviderRoadmap
{
    /**
     * @var
     */
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
     * @return JsonResponse
     * @since v1.0.20
     * @version v1.0.20
     */
    public function generateReport($result, $data): JsonResponse
    {
        return response()->json([
            'status' => 'response',
            'response' => $result,
            'provider' => get_class($this),
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ]);
    }

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
     * @throws \JsonException
     * @deprecated
     */
    public function toJson($data)
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
