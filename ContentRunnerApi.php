<?php

class ContentRunnerApi {

    private $client_id     = 'your_api_username';
    private $client_secret = 'your_api_key';

    private $oauth_token;
    private $oauth_expiration;

    private $endpoints = array(
        'auth'    => 'https://api.contentrunner.com/oauth',
        'article' => 'https://api.contentrunner.com/articles',
        'order'   => 'https://api.contentrunner.com/orders',
    );

    public function __construct($token = null, $expiration = null)
    {
        $this->oauth_token      = $token;
        $this->oauth_expiration = $expiration;
    }

    public function get_article_details($filter = array(), $single = false)
    {
        if( !$this->authenticate() ) {
            throw new Exception('OAuth2 authentication failed to return expected access_token');
        }
        if( $single ) {
            if( !is_numeric($filter) ) {
                throw new Exception('Invalid article ID');
            }
            $endpoint = "{$this->endpoints['article']}/$filter}";
        } else {
            $query_string = '';
            if( !empty($filter) && is_array($filter) ) {
                $query_string = '?' . http_build_query($filter);
            }
            $endpoint = "{$this->endpoints['article']}{$query_string}";
        }

        return $this->api_call($endpoint);
    }

    public function post_order($order_details)
    {
        if( !$this->authenticate() ) {
            throw new Exception('OAuth2 authentication failed to return expected access_token');
        }

        return $this->api_call($this->endpoints['order'], $order_details);
    }

    private function authenticate()
    {
        if( !is_null($this->oauth_expiration) && date('Y-m-d H:i') < $this->oauth_expiration ) {
            return true;
        }

        $postdata = array(
            'grant_type' => 'client_credentials'
        );

        $results = $this->api_call($this->endpoints['auth'], $postdata, true);

        if( isset($results['access_token']) ) {

            $this->oauth_token      = $results['access_token'];
            $valid_for              = isset($results['expires_in']) ? $results['expires_in'] : 0;
            $this->oauth_expiration = date('Y-m-d H:i', strtotime("+{$valid_for} seconds"));

            return true;
        }

        return false;
    }

    private function api_call($endpoint, $postdata = null, $auth_call = false)
    {
        $curl_options = array(
            CURLOPT_URL            => $endpoint,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            )
        );

        // send credentials to request OAuth token, otherwise include bearer token
        if( $auth_call ) {
            $curl_options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $curl_options[CURLOPT_USERPWD]  = "{$this->client_id}:{$this->client_secret}";
        } else {
            $curl_options[CURLOPT_HTTPHEADER][] = "Authorization: Bearer {$this->oauth_token}";
        }

        if( !is_null($postdata) ) {
            $postdata       = json_encode($postdata);
            $curl_options[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . strlen($postdata);
            $curl_options[CURLOPT_POST]         = 1;
            $curl_options[CURLOPT_POSTFIELDS]   = $postdata;
        } else {
            $curl_options[CURLOPT_POST] = 0;
        }

        $ch = curl_init();

        curl_setopt_array($ch, $curl_options);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if( !$response ) {
            $error = curl_error($ch);
            throw new Exception("Curl error: {$error}");
        }

        if( $status < 200 || $status >= 300 ) {
            return $response;
        }

        $result_arr = json_decode($response, true);

        if( is_null($result_arr) ) {
            ob_start();
            var_dump($response);
            $result = ob_get_clean();
            throw new Exception("Malformed JSON response: {$result}");
        }

        return $result_arr;
    }

}
