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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Winefing\ApiBundle\Entity\CharacteristicCategory;
use AppBundle\Form\CharacteristicCategoryTrType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EventRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',  HiddenType::class, array(
                'required' => false))
            ->add('budget', NumberType::class)
            ->add('peopleNumber', NumberType::class)
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('duration', NumberType::class)
            ->add('email', EmailType::class)
            ->add('phoneNumber', null)
            ->add('description', TextareaType::class)
            ->add('eventCategory', EntityType::class,  array(
                'class' => 'WinefingApiBundle:EventCategory',
                'choice_label' => 'id', 'required' => false));
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\EventRequest',
        ));
    }
}