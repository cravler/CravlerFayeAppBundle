<?php

namespace Cravler\FayeAppBundle\Service;

use Nc\FayeClient\Client;
use Nc\FayeClient\Adapter\CurlAdapter;
use Cravler\FayeAppBundle\Client\ClientInterface;
use Cravler\FayeAppBundle\Ext\SystemExtInterface;
use Cravler\FayeAppBundle\Service\ExtensionsChain;
use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class EntryPointManager
{
    /**
     * @var SecurityManager
     */
    private $sm;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ExtensionsChain
     */
    private $extChain;

    /**
     * @var string
     */
    private $entryPointPrefix;

    /**
     * @param SecurityManager $sm
     * @param ClientInterface $client
     * @param ExtensionsChain $extChain
     * @param string          $entryPointPrefix
     */
    public function __construct(SecurityManager $sm, ClientInterface $client, ExtensionsChain $extChain, $entryPointPrefix = '')
    {
        $this->sm = $sm;
        $this->client = $client;
        $this->extChain = $extChain;
        $this->entryPointPrefix = $entryPointPrefix;
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
     */
    public function publish(EntryPointInterface $entryPoint, $channel, $data = null)
    {
        $channel = '/' . str_replace('.', '~', $this->entryPointPrefix . '@' . $entryPoint->getId()) . $channel;
        $this->client->send($channel, $data, $this->getExt());
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