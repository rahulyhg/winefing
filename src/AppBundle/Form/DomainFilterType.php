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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Winefing\ApiBundle\Repository\TagRepository;
use Winefing\ApiBundle\Repository\WineRegionRepository;


class DomainFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, ['required'=>false,'label'=>false, 'format'=>'dd-MM-yyyy', 'widget' => 'single_text', 'html5'=> false, 'attr'=>['class'=>'input-sm form-control']])
            ->add('endDate', DateType::class, ['required'=>false,'label'=>false, 'format'=>'dd-MM-yyyy', 'widget' => 'single_text', 'html5'=> false, 'attr'=>['class'=>'input-sm form-control']])
            ->add('peopleNumber', ChoiceType::class, [
                'choices' => [1=>1, 2=>2, 3=>3, 4=>4,5=> 5, "6+"=>6],
                'attr'=> array('class'=> 'selectpicker','data-dropup-auto'=>"false")])
            ->add('wineRegion', EntityType::class,  array(
                'class' => 'WinefingApiBundle:WineRegion',
                'multiple'=>"true",
                'required'=>false,
                'attr'=> array('class'=> 'selectpicker','data-dropup-auto'=>"false"),
                'placeholder'=>'',
                'query_builder' => function (WineRegionRepository $er) {
                    return $er->createQueryBuilder('wineRegion')
                        ->join('wineRegion.domains', 'domain')
                        ->join('domain.properties', 'property')
                        ->join('property.rentals', 'rental')
                        ->groupBy('wineRegion.id');
                },
                'choice_label' => function ($wineRegion) use ($options) {
                    return $wineRegion->getDisplayName($options['language']);
                }))
            ->add('tags', EntityType::class,  array(
                'class' => 'WinefingApiBundle:Tag',
                'required'=>false,
                'multiple'=>"true",
                'attr'=> array('required'=>'false','class'=> 'selectpicker','data-dropup-auto'=>"false"),
                'placeholder'=>'',
                'query_builder' => function (TagRepository $er) {
                    return $er->createQueryBuilder('tag')
                        ->join('tag.domains', 'domain')
                        ->groupBy('tag.id');
                },
                'choice_label' => function ($tag) use ($options) {
                    return $tag->getDisplayName($options['language']);
                }))
            ->add('submit', SubmitType::class, array('attr'=>array('class'=>'btn btn-primary btn-block')))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'language' => 'fr'
        ));
    }
}