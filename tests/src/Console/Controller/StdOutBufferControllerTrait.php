<?php

namespace leapsunit\src\Console\Controller;

/**
 * StdOutBufferControllerTrait is a trait, which can be applied to [[Leaps\Console\Controller]],
 * allowing to store all output into internal buffer instead of direct sending it to 'stdout'
 */
trait StdOutBufferControllerTrait
{
    /**
     * @var string output buffer.
     */
    private $stdOutBuffer = '';

    public function stdout($string)
    {
        $this->stdOutBuffer .= $string;
    }

    public function flushStdOutBuffer()
    {
        $result = $this->stdOutBuffer;
        $this->stdOutBuffer = '';
        return $result;
    }
}