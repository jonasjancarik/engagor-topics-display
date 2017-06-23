<?php

class Engagor {

    const API_URI = 'https://api.engagor.com';
    const REQUEST_TIMEOUT = 30;

    private $accessToken;

    public function __construct($accessToken) {
        $this->accessToken = $accessToken;
    }

    /**
     * Perform an API request.
     * GET requests only, for now. Hack it!
     *
     * @param string $endpoint The endpoint to call.
     * @param array[optional] $parameters The parameters to send with the request.
     * @return string
     */
    public function api($endpoint, $parameters = array()) {
        $url = $this->buildUrl($endpoint, $parameters);
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => self::REQUEST_TIMEOUT,
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if (!empty($errno) || !empty($error)) {
            throw new Exception(sprintf('cURL error %s: %s', $errno, $error));
        }
        return $response;
    }

    /**
     * Process the response received from the API.
     *
     * @param string $response The JSON encoded response.
     * @return array
     */
    public function processResponse($response) {
        $response = json_decode($response, true);
        $error = json_last_error();

        if ($error != JSON_ERROR_NONE) {
            switch ($error) {
                case JSON_ERROR_DEPTH:
                    $message = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $message = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $message = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $message = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $message = 'Unknown error';
                    break;
            }
            throw new Exception(sprintf('JSON error %s: %s', $error, $message));
        }
        return $response;
    }

    /**
     * Build the URL to call.
     *
     * @param string $endpoint
     * @param array[optional] $parameters The parameters to send with the request.
     * @return string
     */
    private function buildUrl($endpoint, $parameters = array()) {
        $endpoint = ltrim($endpoint, '/');
        $format = empty($parameters) ? '%s/%s?access_token=%s' : '%s/%s?access_token=%s&%s';

        return sprintf(
            $format,
            self::API_URI,
            $endpoint,
            $this->accessToken,
            http_build_query($parameters)
        );
    }
}
