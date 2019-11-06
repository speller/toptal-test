<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-10
 * Time: 09:34
 */

namespace App\Response;

/**
 * Data-holder for JSON API results
 */
class JsonData
{
    /** @var null */
    public $data = null;
    /** @var bool */
    public $success = false;
    /** @var null */
    public $msg = null;
    /** @var int */
    public $errorCode = 0;

    /**
     * Creates successful result
     * @param mixed $result
     * @return JsonData
     */
    public static function data($result): JsonData
    {
        $r = new self();
        $r->data = $result;
        $r->success = true;
        return $r;
    }

    /**
     * Creates error result
     * @param string $msg
     * @param int $code
     * @return JsonData
     */
    public static function error(string $msg, int $code = -1): JsonData
    {
        $r = new self();
        $r->msg = $msg;
        $r->errorCode = $code;
        return $r;
    }
}