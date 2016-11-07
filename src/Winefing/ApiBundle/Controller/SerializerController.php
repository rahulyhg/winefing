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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SerializerController {

    public function serialize($obj, $format = 'json') {
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($obj, $format);
        return $json;
    }

    public function decode($json, $format = 'json'){
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer(array($normalizer), array($encoder));
        $array = $serializer->decode($json, $format);
        return $array;
    }
    public function deserialize($json, $pathToClass, $format = 'json'){
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer(array($normalizer), array($encoder));
        $object = $serializer->deserialize($json, $pathToClass, $format);
        return $object;

    }
}

