<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 15/09/2016
 * Time: 14:51
 */
namespace Winefing\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ApiController {

    public function post($url, $params, $file) {
        foreach ($params as $key => $value){
            $body[] = [
                'name' => $key,
                'contents' => $value
            ];
        }
        if($file!= null) {
            $body[] = [
                'name' => 'picture',
                'contents' => fopen($file->getRealPath(), "r"),
                'filename' => $file->getClientOriginalName(),
                'Content-type' => 'multipart/form-data'
            ];
        }
        $client = new Client();
        $client->request('POST', $url, ['multipart' => $body]);
    }

    public function put(){

    }
}
