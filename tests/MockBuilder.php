<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-17
 * Time: 19:38
 */

namespace App\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Customized mock builder
 */
class MockBuilder extends \PHPUnit\Framework\MockObject\MockBuilder
{
    /**
     * @var array|string
     */
    private $className;

    /**
     * @param TestCase     $testCase
     * @param array|string $className
     */
    public function __construct(TestCase $testCase, $className)
    {
        parent::__construct($testCase, $className);
        $this->className = $className;
    }

    /**
     * @throws \ReflectionException
     */
    public function mockAllProtectedMethods()
    {
        $reflection = new \ReflectionClass($this->className);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PROTECTED);
        return
            $this->setMethods(
                array_map(
                    function(\ReflectionMethod $method) {
                        return $method->getName();
                    },
                    $methods
                )
            );
    }
}