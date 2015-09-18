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
        $this->data[] = array(
            'url'  => $url,
            'body' => $body,
        );
    }

    /**
     * @param Event $event
     */
    public function onTerminate(Event $event)
    {
        if (!count($this->data)) {
            return;
        }

        foreach ($this->data as $message) {
            $this->adapter->postJSON($message['url'], $message['body']);
        }
        $this->data = array();
    }
}
