<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

use Nc\FayeClient\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class TerminateAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function postJSON($url, $body)
    {
        if (!isset($this->data[$url])) {
            $this->data[$url] = array();
        }
        $this->data[$url][] = $body;
    }

    public function postData()
    {
        if (!count($this->data)) {
            return;
        }

        foreach ($this->data as $url => $data) {
            if ($this->adapter instanceof CurlAdapter) {
                $this->adapter->postJSON($url, $data);
            } else {
                foreach ($data as $body) {
                    $this->adapter->postJSON($url, $body);
                }
            }
        }
        $this->data = array();
    }

    /**
     * @param Event $event
     */
    public function onTerminate(Event $event)
    {
        $this->postData();
    }
}
