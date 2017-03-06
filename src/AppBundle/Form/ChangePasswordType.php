<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 13:23
 */

namespace AppBundle\Form;


namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentPassword', PasswordType::class, array('required'=>true,
                'label'=>'label.current_password','mapped' => false, 'data'=>'', 'attr'=>array('placeholder'=>'label.current_password', 'class'=> 'form-control')))
            ->add('password', RepeatedType::class, array('type' => PasswordType::class,
                'first_options'  => array('required'=>true, 'label' => 'label.password', 'data'=>'', 'attr'=>array('placeholder'=>'label.password','class'=> 'form-control')),
                'second_options' => array('required'=>true, 'label' => 'label.confirmation_password', 'data'=>'', 'attr'=>array('placeholder'=>'label.confirmation_password','class'=> 'form-control'))))
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
    }
}