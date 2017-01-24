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
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formattedAddress', null, array('label'=>'label.address', 'attr'=>array('class'=>'form-control', 'onFocus'=>"geolocate()", 'required'=>true)))
            ->add('postalCode', null, array('label'=>'label.postal_code','attr'=> array('maxlength'=>"60", 'class'=>'form-control', 'disabled'=>true)))
            ->add('locality', null, array('label'=> 'label.locality', 'attr'=> array('maxlength'=>"60", 'class'=>'form-control', 'disabled'=>true)))
            ->add('country', null, array('label'=>'label.country', 'attr'=> array('maxlength'=>"155", 'class'=>'form-control', 'disabled'=>true, 'required'=>true)))
            ->add('streetAddress', null, array('label'=>'label.street', 'attr'=> array('maxlength'=>"155", 'class'=>'form-control', 'disabled'=>true, 'required'=>true)))
            ->add('route', null, array('label'=>'label.route', 'attr'=> array('maxlength'=>"155", 'class'=>'form-control', 'disabled'=>true, 'required'=>true)))
            ->add('additionalInformation', null, array('label'=>'label.address_additional_information', 'attr'=> array('maxlength'=>"255", 'class'=>'form-control')))
        ;
    } 
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Address',
        ));
    }
}