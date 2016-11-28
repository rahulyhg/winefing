<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class FormatEnum extends BasicEnum {
    const Monnaie = 'MONNAIE';
    const Percentage = 'PERCENTAGE';
    const Text = 'TEXT';
    const Float = 'FLOAT';
    const Int = 'INT';
    const Boolean = 'BOOLEAN';
    const Time = 'TIME';
    const Varchar = 'VARCHAR';
}