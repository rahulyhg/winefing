<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 13:23
 */

namespace Winefing\UserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class HostRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, array('label' => 'label.first_name', 'attr'=> array('maxlength'=>"60",'class'=> 'form-control')))
            ->add('lastName', null, array('label' => 'label.last_name','attr'=> array('maxlength'=>"60",'class'=> 'form-control')))
            ->add('phoneNumber', null, array('label' => 'label.phone_number', 'attr'=> array('maxlength'=>"10",'class'=> 'form-control')))
            ->add('password', RepeatedType::class, array('type' => PasswordType::class,
                'first_options'  => array('label' => 'label.password', 'data'=>'', 'attr'=>array('class'=> 'form-control')),
                'second_options' => array('label' => 'label.confirmation_password', 'data'=>'', 'attr'=>array('class'=> 'form-control'))))
            ->add('email', RepeatedType::class, array('type' => EmailType::class,
                'first_options'  => array('label' => 'label.email', 'data'=>'', 'attr'=>array('class'=> 'form-control')),
                'second_options' => array('label' => 'label.confirmation_email', 'data'=>'', 'attr'=>array('class'=> 'form-control'))))
            ->add('submit', SubmitType::class, array('label' => 'label.submit',
                'attr' => array('class' => 'btn btn-primary pull-right')))

        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\User',
        ));
    }
}