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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\AreaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Winefing\ApiBundle\Entity\Format;

class CharacteristicValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $valueType = $options["valueType"];
        $valueTypeLabel = $options["valueTypeLabel"];
        $builder
            ->add('id',  HiddenType::class, array(
                'required' => false, 'block_name' => 'custom_name'))
            ->add('characteristic',  HiddenType::class, array('required' => false, 'data' => $options["characteristic"], 'block_name' => 'custom_name'));
        switch ($valueType) {
            case Format::Text:
                $builder->add('value', TextareaType::class, array('attr'=>['class'=> 'form-control'], 'label' => $valueTypeLabel, 'block_name' => 'custom_name'));
                break;
            case Format::Varchar:
                $builder->add('value', TextType::class, array('attr'=>['class'=> 'form-control'], 'label' => $valueTypeLabel));
                break;
            case Format::Int:
                $builder->add('value', IntegerType::class, array('attr'=>['class'=> 'form-control'], 'label' => $valueTypeLabel));
                break;
            case Format::Percentage:
                $builder->add('value', PercentType::class, array('attr'=>['class'=> 'form-control'], 'label' => $valueTypeLabel));
                break;
            case Format::Boolean:
                $builder->add('value', CheckboxType::class, array('label' => $valueTypeLabel, 'required' => false));
                break;
            case Format::Time:
                $builder->add('value', TimeType::class, array('label' => $valueTypeLabel));
                break;
        }
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\CharacteristicValue',
            'characteristic' => '',
            'valueType' => '',
            'valueTypeLabel' => '',
        ));
    }

    public function getName() {
        return null;
    }
}