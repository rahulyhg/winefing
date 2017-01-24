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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class DomainRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('attr'=> array('maxlength'=>"60", 'class'=> 'form-control')))
            ->add('wineRegion', EntityType::class,  array('class' => 'WinefingApiBundle:WineRegion', 'attr'=> array('class'=> 'form-control'),
                'choice_label' => function ($wineRegion) use ($options) {
                    return $wineRegion->getDisplayName($options['language']);
                }))
            ->add('address', AddressType::class)
            ->add('user', HostUserRegistrationType::class)
            ->add('agree', CheckboxType::class, array('label'=>false, 'mapped'=>false, 'required'=>true))
            ->add('subscription', CheckboxType::class, array('label'=>false, 'mapped'=>false, 'required'=>false, 'attr'=>array('checked'=>true)))
            ->add('submit', SubmitType::class, array('label' => 'label.submit',
                'attr' => array('class' => 'btn btn-primary pull-right')))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Domain',
            'language'=>'fr'
        ));
    }
}