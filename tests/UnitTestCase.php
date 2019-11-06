<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-17
 * Time: 19:25
 */

namespace App\Tests;

use App\Utils\Commons;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Base test case class with basic routines
 */
class UnitTestCase extends TestCase
{
    /**
     * Call original method code on a mock instead of mocked code.
     * Useful to call mocked protected methods.
     * @param MockObject $mock
     * @param string $methodName
     * @param array $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    public function callOriginalMethod($mock, string $methodName, array $arguments = [])
    {
        $reflection = new \ReflectionClass($mock);
        $reflection = $reflection->getParentClass();
        $reflectionMethod = $reflection->getMethod($methodName);
        $reflectionMethod->setAccessible(true);
        return
            $reflectionMethod->invokeArgs(
                $mock,
                $arguments
            );
    }

    /**
     * @param string|string[] $className
     * @return MockBuilder
     */
    public function getMockBuilder($className): \PHPUnit\Framework\MockObject\MockBuilder
    {
        return new MockBuilder($this, $className);
    }

    /**
     * Invocation matcher which match only on the specified call of the specified method
     * @param string $methodName
     * @param int $callIndex
     * @return InvokedAtMethodIndex
     */
    public function atCallIdx(string $methodName, int $callIndex)
    {
        return new InvokedAtMethodIndex($methodName, $callIndex);
    }

    /**
     * Returns list of protected methods for the specified class
     * @param string $className
     * @return string[]
     * @throws \ReflectionException
     */
    public function getProtectedMethods(string $className): array
    {
        $reflection = new \ReflectionClass($className);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PROTECTED);
        return
            array_map(
                function (\ReflectionMethod $method) {
                    return $method->getName();
                },
                $methods
            );
    }

    /**
     * Creates and returns temp directory for the current class.
     * Optionally clears its contents.
     * @param bool $clear
     * @return string
     */
    public function getTempDir(bool $clear): string
    {
        $dir = __DIR__ . '/../var/' . str_replace('\\', '_', get_class($this));
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        } elseif ($clear) {
            Commons::removeDirWithFiles($dir);
            mkdir($dir, 0777, true);
        }
        return $dir;
    }
}