<?php

namespace Cravler\FayeAppBundle\Service;

use Cravler\FayeAppBundle\Ext\ContributorInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class ExtensionsChain
{
    /**
     * @var array
     */
    private $extensions = array();

    /**
     * @param $extension
     */
    public function addExtension($extension)
    {
        if ($extension instanceof ContributorInterface) {
            $this->extensions[] = $extension;
        }
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}
