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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Winefing\ApiBundle\Entity\CharacteristicCodeEnum;

class CharacteristicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',  HiddenType::class, array('required' => false))
            ->add('code', ChoiceType::class, array('placeholder'=>'', 'choices' => array(CharacteristicCodeEnum::WineType => CharacteristicCodeEnum::WineType)))
            ->add('format', EntityType::class,  array(
                'class' => 'WinefingApiBundle:Format',
                'choice_label' => 'name'))
            ->add('picture', FileType::class, ['required' => false, 'data_class' => null])
            ->add('characteristicTrs', CollectionType::class, array(
                'entry_type' => CharacteristicTrType::class))
            ->add('activated', CheckboxType::class, ['required' => false])
            ->add('characteristicCategory', EntityType::class,  array(
                'class' => 'WinefingApiBundle:characteristicCategory',
                'choice_label' => 'id'))

        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Characteristic',
        ));
    }
}