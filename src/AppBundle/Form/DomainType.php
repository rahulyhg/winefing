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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use AppBundle\Form\AddressType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class DomainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('attr'=> array('maxlength'=>"60")))
            ->add('description', TextareaType::class, array('attr'=> array('maxlength'=>"3000", 'style' => 'height:250px')))
            ->add('history', TextareaType::class, array('label'=>'label.domain_history','required' => false, 'attr'=> array('maxlength'=>"3000", 'style' => 'height:250px')))
            ->add('wineRegion', EntityType::class,  array('class' => 'WinefingApiBundle:WineRegion', 'attr'=> array('class'=> 'selectpicker'),
                'choice_label' => function ($wineRegion) use ($options) {
                    return $wineRegion->getDisplayName($options['language']);
                }))
            ->add('tags', EntityType::class,  array('class' => 'WinefingApiBundle:Tag','attr'=> array('multiple'=>true,'class'=> 'selectpicker'),
                'choice_label' => function ($tag) use ($options) {
                    return $tag->getDisplayName($options['language']);
                }));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Domain',
            'language' => 'fr'
        ));
    }
}