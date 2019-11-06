<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-10
 * Time: 09:22
 */

namespace App\Utils;

use App\Exception\RequestFailException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Utilities for input parameters parsing routines.
 */
class InputParamUtils
{
    /**
     * Parse raw input parameter as JSON data
     * @param $data
     * @return mixed
     * @throws RequestFailException
     */
    public static function parseAsJson($data)
    {
        if (!is_string($data)) {
            throw new \RuntimeException('Invalid input data');
        }
        try {
            $data = json_decode($data, false, 10, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RequestFailException('Invalid input data (must be in JSON format)', 0, $e);
        }
        return $data;
    }

    /**
     * Returns JSON from request body
     * @param Request $request
     * @return mixed
     * @throws RequestFailException
     */
    public static function parseJsonRequest(Request $request)
    {
        return self::parseAsJson($request->getContent());
    }
}
