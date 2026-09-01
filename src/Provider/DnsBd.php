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


use Xenon\LaravelBDSms\Handler\RenderException;

/**
 * Class DnsBd
 *
 * This provider has never been implemented - it carries no api endpoint and no
 * credentials. Until it is written, it refuses loudly rather than returning null
 * and letting the caller believe an sms went out.
 *
 * @package Xenon\LaravelBDSms\Provider
 */
class DnsBd extends AbstractProvider
{

    /**
     * @throws RenderException
     */
    public function sendRequest()
    {
        $this->errorException();
    }

    /**
     * @throws RenderException
     */
    public function errorException()
    {
        throw new RenderException('DnsBd provider is not implemented yet, so no sms can be sent through it.
        Pick another provider from config/sms.php.');
    }
}
