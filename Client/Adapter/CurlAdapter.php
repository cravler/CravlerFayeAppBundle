<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class CurlAdapter implements BatchAdapterInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function postJSON($url, $data)
    {
        if (!is_array($data)) {
            $data = array($data);
        }

        if (function_exists('exec')) {
            $cmd = "";
            foreach ($data as $key => $body) {
                $body = str_replace('\'', '\u0027', $body);

                if ($key) {
                    $cmd .= " ; ";
                }
                $cmd .= "curl -X POST";
                if (isset($this->config['connect_timeout'])) {
                    $cmd .= " --connect-timeout " . $this->config['connect_timeout'];
                }
                $cmd .= " -H 'Content-Type: application/json'";
                $cmd .= " -H 'Content-Length: " . strlen($body) . "'";
                $cmd .= " -d '" . $body . "' " . "'" . $url . "'";
                $cmd .= " > /dev/null 2>&1";
            }
            $cmd .= " &";
            exec($cmd, $output, $exit);

        } else {
            $mh = curl_multi_init();
            foreach ($data as $key => $body) {
                $curl = curl_init($url);
                if (isset($this->config['connect_timeout'])) {
                    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->config['connect_timeout']);
                }
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($body),
                ));
                curl_multi_add_handle($mh, $curl);
            }
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while($running > 0);
            curl_multi_close($mh);
        }
    }
}
