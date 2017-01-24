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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class HostUserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, array('label' => 'label.first_name', 'attr'=> array('required'=>true,'maxlength'=>"60",'class'=> 'form-control')))
            ->add('lastName', null, array('label' => 'label.last_name','attr'=> array('required'=>true,'maxlength'=>"60",'class'=> 'form-control')))
            ->add('phoneNumber', null, array('label' => 'label.phone_number', 'attr'=> array('required'=>true,'class'=> 'form-control')))
            ->add('password', RepeatedType::class, array('type' => PasswordType::class,
                'first_options'  => array('label' => 'label.password', 'data'=>'', 'attr'=>array('required'=>true,'class'=> 'form-control', 'placeholder'=>'label.enter_password')),
                'second_options' => array('label' => false, 'data'=>'', 'attr'=>array('required'=>true,'class'=> 'form-control', 'placeholder'=>'label.confirmation_password'))))
            ->add('email', RepeatedType::class, array('type' => EmailType::class,
                'first_options'  => array('label' => 'label.email', 'data'=>'', 'attr'=>array('required'=>true,'class'=> 'form-control','placeholder'=>'label.enter_email')),
                'second_options' => array('label' => false, 'data'=>'', 'attr'=>array('required'=>true,'class'=> 'form-control', 'placeholder'=>'label.confirmation_email'))))


        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\User',
        ));
    }
}