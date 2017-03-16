<?php

namespace AppBundle\Controller;

use AppBundle\Form\DomainFilterType;
use AppBundle\Form\UserRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Winefing\ApiBundle\Entity\User;

class HomeController extends Controller
{
    /**
     * @Route("/home", name="home")
     */
    public function indexAction(Request $request)
    {
        $api = $this->container->get("winefing.api_controller");
        $serializer = $this->container->get("jms_serializer");

        $filterForm = $this->createForm(DomainFilterType::class, null, array('language'=>$request->getLocale(), 'action' => $this->generateUrl('domains_by_criteria'),
            'method' => 'GET',
        ));
        //remove the tag field
        $filterForm->remove('tags');
        $filterForm->handleRequest($request);
        if($filterForm->isSubmitted() && $filterForm->isValid()) {

        }

        //get the domain tag
        //get the tags
        $response = $api->get($this->get('router')->generate('api_get_tags_domains', array('language'=>$request->getLocale())), array('maxResult'=> 6));
        $tags = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Tag>', 'json');

        //get the tags
        $response = $api->get($this->get('router')->generate('api_get_wine_region_by_name', array('language'=>$request->getLocale(), 'name'=> 'bordelais')));
        $wineRegion = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\WineRegion', 'json');
//        $token = ->findOneByUser(98);
//        $this->token = $this->getToken($token->getToken());


//        $params = [
//            'grant_type' => "refresh_token",
//            'client_id' => "21_3dvsds11kmo0oosgcokcg40gcgg8w40s0sc000o8cwggso4sws",
//            'client_secret' => "c0chzlnmf34ggckssk80os8coksgoss0s48ockw808c488c0s",
//            'refresh_token' => 'MDlkNWU1MDdlNjU0NWNlMjlmMzliMzk1NmEwZTA0ZmY4NjhlNjgxZDMyOTE2ZTc1MDAyNDc0YmZjYWEyODQ4Nw',
//        ];
//        $response = $client->request('POST', "https://dev.winefing.fr/winefing/web/app_dev.php/oauth/v2/token", ['json' => $params]);
//        $serializer = $this->container->get('jms_serializer');
//        var_dump($response->getBody()->getContents());
//        $tokenManager = $this->get('fos_oauth_server.access_token_manager.default');
//        $token        = $this->get('security.token_storage')->getToken();
//        $accessToken  = $tokenManager->findTokenBy(array("user"=>$token->getUser()));
//        var_dump($accessToken->getToken());
//        var_dump($accessToken == NULL);

//        $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
//        $client = $clientManager->createClient();
//        $client->setAllowedGrantTypes(array('password', 'refresh_token'));
//        $clientManager->updateClient($client);
//        var_dump($client->getPublicId());
////        $publicId = "19_3j9bj77ogpuso00gwo0ows48okwkckc8ok8o4044cwkkkogs8g";
//        $client = new GuzzleHttp\Client();
//        $params = [
//            'grant_type' => "password",
//            'client_id' => "20_649vmfgw4csocck0cc0cckwg444s88sgococo4s488cs48k88g",
//            'client_secret' => "4qda3cgqgjcw00koookc8sk0sc04k4oss8oo4swskw884s040o",
//                'username' => 'blabla@gmail.com',
//                'password' => 'Pouca26059!'
//        ];
////
//        $response = $client->request('POST', "https://dev.winefing.fr/winefing/web/app_dev.php/oauth/v2/token", ['json' => $params]);
//        var_dump($response->getBody()->getContents());
//        $client = new GuzzleHttp\Client();
//        //    $client = new GuzzleHttp\Client();
//        $body =[
//            'form_params' =>
//                [
//                    'grant_type' => 'password',
//                    'client_id' => '429n7l7hjjqc8w808g80s8oss8goc4g0kk0w8kgsk8wss4cwcc',
//                    'client_secret' => '1geviquj6d8kckkscogckwg404k0c8ks4kog00gs84kw8wgowk',
//                    'username' => 'blabla@gmail.com',
//                    'password' => 'Pouca260594!'
//                ]
//        ];
//        $token = $client->request(
//            'POST',
//            'https://dev.winefing.fr/winefing/web/app_dev.php/oauth/v2/token',
//            $body
//        );
//        var_dump($response->getBody()->getContents());
        return $this->render('index.html.twig', array('filterForm'=>$filterForm->createView(), 'tags'=>$tags, 'wineRegion'=>$wineRegion));
    }


}
