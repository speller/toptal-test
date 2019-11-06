<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-11
 * Time: 11:14
 */

namespace App\Controller;

use App\Response\JsonData;

/**
 * Controller for health checking purposes
 */
class HealthCheckController
{
    private $version;

    /**
     * HealthCheckController constructor.
     * @param $version
     */
    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * Health check method. Returns OK to show caller application is working good.
     * @return JsonData
     */
    public function healthCheck()
    {
        return JsonData::data([
            'revision' => $this->version,
        ]);
    }
}