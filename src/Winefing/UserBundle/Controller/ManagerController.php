<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 30/12/2016
 * Time: 10:06
 */

namespace Winefing\UserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerController extends Controller
{
    /**
     * @Route("users/{group}", name="users_by_group")
     */
    public function cget($group) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_host_users'));
        $users= $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\User>', 'json');
        return $this->render('admin/user/index.html.twig', array(
            'users' => $users
        ));
    }

}