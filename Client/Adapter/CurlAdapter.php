<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class CurlAdapter implements BatchAdapterInterface
{
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
