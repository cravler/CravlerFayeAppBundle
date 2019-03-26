<?php

namespace Cravler\FayeAppBundle\Package;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Package
{
    /**
     * @var mixed
     */
    private $body;

    /**
     * @param mixed $body
     */
    public function __construct($body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->body);
    }
}
