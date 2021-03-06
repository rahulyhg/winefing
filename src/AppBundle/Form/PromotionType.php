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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use AppBundle\Form\LanguageType;
use Winefing\ApiBundle\Entity\Language;
use Winefing\ApiBundle\Entity\FormatEnum;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',  HiddenType::class, array(
                'required' => false))
            ->add('amount', IntegerType::class, array('scale' => 2))
            ->add('minAmount', IntegerType::class, array('scale' => 2, 'required'=>false))
            ->add('format', ChoiceType::class, array('choices' =>
                    array(FormatEnum::Monnaie => FormatEnum::Monnaie, FormatEnum::Percentage => FormatEnum::Percentage)))
            ->add('code', TextType::class)
            ->add('startDate', DateType::class, array('data' => new \DateTime()))
            ->add('endDate', DateType::class, array('required'=>false))
            ->add('firstOrder', CheckboxType::class, array('required'=>false))
            ->add('freeShipping', CheckboxType::class, array('required'=>false))
            ->add('numberDisponible', IntegerType::class, array('required'=>false))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Promotion',
        ));
    }
}