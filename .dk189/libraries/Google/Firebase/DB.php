<?php
namespace Google\Firebase;

class DB {
    private $endpoint;
    private $server_token;

    private $cli;

    public function __construct ($endpoint, $server_token) {
        $this->endpoint = $endpoint;
        $this->server_token = $server_token;

        $this->cli = new \Curl\Client();
    }

    public function set ($collection, $object_id, $data) {
        return $this->cli->put(
            sprintf(
                "https://%s/%s/%s.json?auth=%s",
                $this->endpoint,
                $collection,
                $object_id,
                $this->server_token
            ),
            $data
        );
    }
    public function get ($collection, $object_id) {
        return $this->cli->get(
            sprintf(
                "https://%s/%s/%s.json?auth=%s",
                $this->endpoint,
                $collection,
                $object_id,
                $this->server_token
            )
        );
    }
}
