<?php

namespace Cravler\FayeAppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;
use Cravler\FayeAppBundle\Ext\SystemExtInterface;
use Cravler\FayeAppBundle\Package\Package;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class EntryPointManager
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

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
     * @var string
     */
    private $securityUrlSalt;

    /**
     * @param UrlGeneratorInterface $router
     * @param PackageManager $pm
     * @param SecurityManager $sm
     * @param ExtensionsChain $extChain
     * @param string $entryPointPrefix
     * @param string $securityUrlSalt
     */
    public function __construct(
        UrlGeneratorInterface $router,
        PackageManager $pm,
        SecurityManager $sm,
        ExtensionsChain $extChain,
        $entryPointPrefix = '',
        $securityUrlSalt = ''
    )
    {
        $this->pm = $pm;
        $this->sm = $sm;
        $this->router = $router;
        $this->extChain = $extChain;
        $this->entryPointPrefix = $entryPointPrefix;
        $this->securityUrlSalt = $securityUrlSalt;
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

        $security = array(
            'system' => $this->getSecurityManager()->createSystemToken(),
            'url' => $this->router->generate('faye_app_security', [], 0),
        );

        $security['url.hash'] = md5($security['system'] . ';' . $security['url'] . ';' . $this->securityUrlSalt);

        $ext = array_replace_recursive($ext, array(
            'security' => $security,
        ));

        return $ext;
    }
}
