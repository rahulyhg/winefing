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
        return $client->request('POST', $url, ['form_params' => $params]);
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
        return $client->request('PUT', $url, ['form_params' => $body]);
    }

    public function get($url){
        $client = new Client();
        return $client->request('GET', $url);
    }

    public function delete($url){
        $client = new Client();
        return $client->request('GET', $url);
    }
}



//public function getToken($username, $password)
//{
//    $client = new GuzzleHttp\Client();
//    $body =[
//        'form_params' =>
//            [
//                'grant_type' => 'password',
//                'client_id' => '1_3bcbxd9e24g0gk4swg0kwgcwg4o8k8g4g888kwc44gcc0gwwk4',
//                'client_secret' => '4ok2x70rlfokc8g0wws8c8kwcokw80k44sg48goc0ok4w0so0k',
//                'username' => $username,
//                'password' => $password
//            ]
//    ];
//    if (isset($_SERVER['HTTPS']) &&
//        ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
//        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
//        $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
//        $protocol = 'https://';
//    }
//    else {
//        $protocol = 'http://';
//    }
//    $token = $client->request(
//        'POST',
//        $protocol.$_SERVER['HTTP_HOST'].'/app_dev.php/oauth/v2/token',
//        $body
//    );
//
//    return json_decode($token->getBody(), true)['access_token'];
//}

