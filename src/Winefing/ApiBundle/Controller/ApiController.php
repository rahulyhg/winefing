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

    public function getUrl($uri) {
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }
        else {
            $protocol = 'http://';
        }
        return $protocol.$_SERVER['HTTP_HOST'].$uri;
    }

    public function post($uri, $params) {
        foreach ($params as $key => $value) {
            $body[] = [
                'name' => $key,
                'contents' => $value
            ];
        }
        $client = new Client();
        return $client->request('POST',  $this->getUrl($uri), ['form_params' => $params]);
    }

    public function put($uri, $params){
        $body = [];
        foreach ($params as $key => $value){
            $body[$key] = $value;
        }
        $client = new Client();
        return $client->request('PUT', $this->getUrl($uri), ['form_params' => $body]);
    }

    public function get($uri){
        $client = new Client();
        return $client->request('GET', $this->getUrl($uri));
    }

    public function delete($uri){
        $client = new Client();
        return $client->request('DELETE', $this->getUrl($uri));
    }

    public function file($uri, $params, $file) {
        foreach ($params as $key => $value){
            $body[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        $body[] = [
            'name'     => 'picture',
            'contents' => fopen($file['tmp_name'], "r"),
            'filename' => $file['name'],
            'Content-type' => 'multipart/form-data'
        ];
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


