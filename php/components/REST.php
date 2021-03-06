<?php

namespace bmelo\components;

/**
 * Simple Class for REST requests
 */
class REST {

    /**
     * Executes a REST Request using curl
     * @param type $method POST, PUT, GET... etc
     * @param type $url
     * @param type $data array("param" => "value") ==> index.php?param=value
     * @return type
     */
    public static function request($method, $url, $data = false, $options = []) {
        $curl = curl_init();

        curl_setopt_array($curl, $options);

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

}
