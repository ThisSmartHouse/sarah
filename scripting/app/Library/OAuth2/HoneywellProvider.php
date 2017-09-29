<?php 

namespace App\Library\OAuth2;

class HoneywellProvider extends \League\OAuth2\Client\Provider\GenericProvider
{
    protected function getAccessTokenOptions(array $params)
    {
        $options = parent::getAccessTokenOptions($params);
        
        $options['headers']['authorization'] = 'Basic ' . base64_encode(implode(':', [
            $this->clientId,
            $this->clientSecret
        ]));
        
        return $options;
    }
}
