<?php

namespace Cravler\FayeAppBundle\Service;

use Cravler\FayeAppBundle\Package\Package;
use Cravler\FayeAppBundle\Ext\SystemExtInterface;
use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class EntryPointManager
{
    /**
     * @var PackageManager
     */
    private $pm;

    /**
     * @var SecurityManager
     */
    private $sm;

    /**
     * @var ExtensionsChain
     */
    private $extChain;

    /**
     * @var string
     */
    private $entryPointPrefix;

    /**
     * @param PackageManager $pm
     * @param SecurityManager $sm
     * @param ExtensionsChain $extChain
     * @param string $entryPointPrefix
     */
    public function __construct(PackageManager $pm, SecurityManager $sm, ExtensionsChain $extChain, $entryPointPrefix = '')
    {
        $this->pm = $pm;
        $this->sm = $sm;
        $this->extChain = $extChain;
        $this->entryPointPrefix = $entryPointPrefix;
    }

    /**
     * @return PackageManager
     */
    public function getPackageManager()
    {
        return $this->pm;
    }

    /**
     * @return SecurityManager
     */
    public function getSecurityManager()
    {
        return $this->sm;
    }

    /**
     * @param EntryPointInterface $entryPoint
     * @param string $channel
     * @param mixed $data
     * @return Package
     */
    public function createPackage(EntryPointInterface $entryPoint, $channel, $data = null)
    {
        return new Package(array(
            'channel' => $this->prepareChannel($entryPoint, $channel),
            'data' => $data,
            'ext' => $this->getExt(),
        ));
    }

    /**
     * @param EntryPointInterface $entryPoint
     * @param string $channel
     * @return string
     */
    private function prepareChannel(EntryPointInterface $entryPoint, $channel)
    {
        return '/' . str_replace('.', '~', $this->entryPointPrefix . '@' . $entryPoint->getId()) . $channel;
    }

    /**
     * @return array
     */
    private function getExt()
    {
        $ext = array();
        foreach ($this->extChain->getExtensions() as $extension) {
            if ($extension instanceof SystemExtInterface) {
                $ext = array_replace_recursive($ext, $extension->getSystemExt());
            }
        }

        $ext = array_replace_recursive($ext, array(
            'security' => array(
                'system' => $this->getSecurityManager()->createSystemToken()
            ),
        ));

        return $ext;
    }
}
