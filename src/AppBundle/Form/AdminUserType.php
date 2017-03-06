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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Winefing\ApiBundle\Entity\UserGroupEnum;


class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new StringToArrayTransformer();
//        $builder->add($builder->create('role', 'choice', array(
//            'label' => 'I am:',
//            'mapped' => true,
//            'expanded' => true,
//            'multiple' => false,
//            'choices' => array(
//                'ROLE_NORMAL' => 'Standard',
//                'ROLE_VIP' => 'VIP',
//            )
//        ))->addModelTransformer($transformer));
        $builder
            ->add('id', HiddenType::class, array('required'=>false))
            ->add('firstName', null, array('attr'=> array('maxlength'=>"60", 'class'=>'form-control')))
            ->add('lastName', null, array('attr'=> array('maxlength'=>"60", 'class'=>'form-control')))
            ->add('phoneNumber', null, array('attr'=> array('maxlength'=>"10", 'class'=>'form-control')))
            ->add('email', EmailType::class, array('attr'=>['class'=>'form-control']))
            ->add($builder->create('roles', ChoiceType::class,  array(
                'choices' => array(UserGroupEnum::Blog => UserGroupEnum::Blog, UserGroupEnum::Managment => UserGroupEnum::Managment)))->addModelTransformer($transformer))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\User',
        ));
    }
}