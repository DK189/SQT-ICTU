<?php
class JsonClient {

    public function Get ($url) {
        $data = file_get_contents($url);

        return json_decode($date);
    }

    public function Post ($url, $data) {
        $data = file_get_contents($url, false, stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $data
            )
        )));

        return json_decode($data);
    }

    public function Execute ($url, $query, $data) {
        if (strpos("?", $url) === false) {
            $url .= "?" . http_build_query($query);
        } else {
            $url .= "&" . http_build_query($query);
        }

        $data = file_get_contents($url, false, stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data)
            )
        )));

        return json_decode($data);
    }
}
?>
