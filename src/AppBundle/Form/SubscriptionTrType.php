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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Winefing\ApiBundle\Entity\CharacteristicCategory;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use AppBundle\Form\LanguageType;
use AppBundle\Form\SubscriptionTr;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class SubscriptionTrType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',  HiddenType::class, array(
                'required' => false))
            ->add('subscription', SubscriptionType::class, array())
            ->add('name', null, array('attr'=> array('maxlength'=>"60")))
            ->add('description', TextareaType::class)
            ->add('language', EntityType::class,  array(
                'class' => 'WinefingApiBundle:Language',
                'choice_label' => 'name'))
            ->add('activated', CheckboxType::class, array('required' => false))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\SubscriptionTr',
        ));
    }
}