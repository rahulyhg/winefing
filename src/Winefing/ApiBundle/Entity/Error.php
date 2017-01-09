<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 08/01/2017
 * Time: 18:44
 */

namespace Winefing\ApiBundle\Entity;


class Error
{
    private $code;

    private $message;

    public function __construct($errorEnum)
    {
        $this->code = $errorEnum['code'];
        $this->message = $errorEnum['message'];

    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

}