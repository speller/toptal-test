<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-18
 * Time: 09:42
 */

namespace App\Tests;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Framework\MockObject\Matcher\Invocation;

class InvokedAtMethodIndex implements Invocation
{
    /**
     * @var int
     */
    private $sequenceIndex;

    /**
     * @var int
     */
    private $currentIndex = -1;
    /**
     * @var string
     */
    private $methodName;

    /**
     * @param string $methodName
     * @param int $sequenceIndex
     */
    public function __construct(string $methodName, int $sequenceIndex)
    {
        $this->sequenceIndex = $sequenceIndex;
        $this->methodName = $methodName;
    }

    public function toString(): string
    {
        return 'method ' . $this->methodName . ' invoked at sequence index ' . $this->sequenceIndex;
    }

    /**
     * @param BaseInvocation $invocation
     * @return bool
     */
    public function matches(BaseInvocation $invocation)
    {
        if ($invocation->getMethodName() == $this->methodName) {
            $this->currentIndex++;
            return $this->currentIndex == $this->sequenceIndex;
        } else {
            return false;
        }
    }

    public function invoked(BaseInvocation $invocation): void
    {
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        if ($this->currentIndex < $this->sequenceIndex) {
            throw new ExpectationFailedException(
                \sprintf(
                    'The expected invocation for method %s at index %s was never reached.',
                    $this->methodName,
                    $this->sequenceIndex,
                )
            );
        }
    }
}