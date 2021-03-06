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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, array('required'=>true, 'label' => 'label.first_name', 'attr'=>array('class'=> 'form-control', 'placeholder'=>'label.first_name')))
            ->add('lastName', null, array('required'=>true, 'label' => 'label.last_name', 'attr'=>array('class'=> 'form-control', 'placeholder'=>'label.last_name')))
            ->add('birthDate', DateType::class, ['label'=>false, 'format'=>'dd-MM-yyyy', 'widget' => 'single_text', 'html5'=> false, 'attr'=>['class'=>'form-control']])
            ->add('password', RepeatedType::class, array('type' => PasswordType::class,
                'first_options'  => array('label' => 'label.password', 'data'=>'', 'attr'=>array('class'=> 'form-control', 'placeholder'=>'label.password')),
                'second_options' => array('label' => false, 'data'=>'', 'attr'=>array('class'=> 'form-control', 'placeholder'=>'label.confirmation_password'))))
            ->add('email', RepeatedType::class, array('type' => EmailType::class,
                'first_options'  => array('label' => 'label.email', 'data'=>'', 'attr'=>array('class'=> 'form-control', 'placeholder'=>'label.email')),
                'second_options' => array('label' => false, 'data'=>'', 'attr'=>array('class'=> 'form-control', 'placeholder'=>'label.confirmation_email'))))
            ->add('agree', CheckboxType::class, array('label'=>false, 'mapped'=>false, 'required'=>true))
            ->add('subscription', CheckboxType::class, array('label'=>false, 'mapped'=>false, 'required'=>false, 'attr'=>array('checked'=>true)))

        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\User',
        ));
    }
}