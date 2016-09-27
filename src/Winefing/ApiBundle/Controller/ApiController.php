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
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ApiController {

    public function post($url, $params, $file) {
        foreach ($params as $key => $value){
            if(is_array($params)) {

            } else {
                $body[] = [
                    'name' => $key,
                    'contents' => $value
                ];
            }

        }
        $body[] = [
            'name' => "top",
            'contents' => [
                'name' => "lolilo",
                'contents' => "a"
            ]
        ];
        if($file!= null) {
            $body[] = [
                'name' => 'picture',
                'contents' => fopen($file->getRealPath(), "r"),
                'filename' => $file->getClientOriginalName(),
                'Content-type' => 'multipart/form-data'
            ];
        }
        $client = new Client();
        //var_dump(json_encode($params , JSON_FORCE_OBJECT));
        //$client->request('POST', $url, ['form_params' => $params]);
        var_dump($client->request('POST', $url, ['form_params' => $params])->getBody()->getContents());
/*        $request = $client->post($url,array(
            'content-type' => 'application/json'
        ),array());
        $request->setBody(json_encode($params , JSON_FORCE_OBJECT)); #set body!
        $response = $request->send();a
        var_dump($response);*/
    }

    public function put($url, $params){
        $body = [];
        foreach ($params as $key => $value){
            $body[$key] = $value;
        }
        $client = new Client();
        $client->request('PUT', $url, ['form_params' => $body]);
    }
}
