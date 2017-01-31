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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityRepository as EntityRepository;
use Winefing\ApiBundle\Repository\RefreshTokenRepository as RefreshTokenRepository;

class ApiController {
    protected $container;
    protected $token = '';

    public function __construct(Container $container, $user, RefreshTokenRepository $refreshTokenRepository)
    {
//        $this->container = $container;
//        $user = $this->container->getParameter('id');
//        if($user instanceof User) {
//            $token = $refreshTokenRepository->findOneByUser(98);
//            $this->token = $this->getToken($token->getToken());
//        }
    }
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
        $client = new Client();
        return $client->request('POST',  $this->getUrl($uri), ['headers' => ['X-Token' => $this->token], 'json' => $params]);
    }
    public function link($uri, $params) {
        $client = new Client();
        return $client->request('LINK',  $this->getUrl($uri), ['headers' => ['X-Token' => $this->token], 'json' => $params]);
    }
    public function patch($uri, $params) {
        $client = new Client();
        return $client->request('PATCH',  $this->getUrl($uri), ['headers' => ['X-Token' => $this->token], 'json' => $params]);
    }
    public function unlink($uri, $params) {
        $client = new Client();
        return $client->request('UNLINK',  $this->getUrl($uri), ['headers' => ['X-Token' => $this->token], 'json' => $params]);
    }

    public function put($uri, $params){
        $client = new Client();
        return $client->request('PUT', $this->getUrl($uri), ['headers' => ['X-Token' => $this->token], 'json' => $params]);
    }

    public function get($uri){
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer '.$this->token
        ];

        return $client->request(
            'GET',
            $this->getUrl($uri),
            array('headers'=>$headers)
        );
    }

    public function delete($uri){
        $client = new Client();
        return $client->request('DELETE', $this->getUrl($uri));
    }

    public function file($uri, $params, $file) {
        $client = new Client();

        foreach ($params as $key => $value){
            $body[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        $body[] = [
            'name'     => 'media',
            'contents' => fopen($file->getRealPath(), "r"),
            'filename' => $file->getClientOriginalName(),
            'Content-type' => 'multipart/form-data'
        ];
        return $client->request('POST',  $this->getUrl($uri), ['multipart' => $body]);
    }

    public function postMailChimp($url, $methode, $bodyJson) {

        $client = new GuzzleHttp\Client();

        $headers = [
            'Authorization' => 'Bearer '.'123',
            'content-type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $test = new GuzzleHttp\Psr7\Request(
            'POST',
            $url,
            $headers,
            $bodyJson
        );

        $promise = $client->send($test);
        return json_decode($promise->getBody(),true);
    }
    public function getClientToken() {
        $client = new GuzzleHttp\Client();
        return $client->request('GET', "https://dev.winefing.fr/winefing/web/app_dev.php".$this->generateUrl('fos_oauth_server_authorize'),
            [
                'client_id'     => '429n7l7hjjqc8w808g80s8oss8goc4g0kk0w8kgsk8wss4cwcc',
                'redirect_uri'  => 'https://dev.winefing.fr/winefing/web/app_dev.php/fr/home',
                'response_type' => 'code'
            ]
        );
    }
    public function getToken($refreshToken){
        $client = new GuzzleHttp\Client();
        $params = [
            'grant_type' => "refresh_token",
            'client_id' => "21_3dvsds11kmo0oosgcokcg40gcgg8w40s0sc000o8cwggso4sws",
            'client_secret' => "c0chzlnmf34ggckssk80os8coksgoss0s48ockw808c488c0s",
            'refresh_token' => $refreshToken,
        ];
        $response = $client->request('POST', "https://dev.winefing.fr/winefing/web/app_dev.php/oauth/v2/token", ['json' => $params]);
        return json_decode($response->getBody(), true)['access_token'];
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


