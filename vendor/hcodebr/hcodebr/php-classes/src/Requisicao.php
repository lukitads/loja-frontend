<?php

namespace Hcode;

class Requisicao
{

    private $payload;
    private $url;
    
    public function __construct(array $payload = [], string $url){
        $this->payload = http_build_query($payload);
        $this->url = $url;
    }

    public function post()
    {
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $this->payload
            )
        );
        
        $context = stream_context_create($opts);
        return file_get_contents($this->url, false, $context);
    }

    public function get() {
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'header'  => 'Content-type: application/x-www-form-urlencoded'
            )
        );
        
        $context = stream_context_create($opts);
        return file_get_contents($this->url, false, $context);
    }
}