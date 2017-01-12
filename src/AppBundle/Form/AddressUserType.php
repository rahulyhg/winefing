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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class AddressUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('required'=>true, 'label'=>'label.name', 'attr'=>array('maxlength'=>"255",'class'=>'form-control','placeholder'=>'example.address_home')))
            ->add('additionalInformation', null, array('label'=>'label.address_additional_information', 'attr'=>array('placeholder'=>'example.address_additional_information','maxlength'=>"255",'class'=>'form-control')))
            ->add('formattedAddress', null, array('label'=>'label.address', 'attr'=>array('maxlength'=>"255", 'class'=>'form-control')))
            ->add('postalCode', null, array('attr'=> array('maxlength'=>"60", 'class'=>'form-control')))
            ->add('locality', null, array('attr'=> array('maxlength'=>"60", 'class'=>'form-control')))
            ->add('country', null, array('attr'=> array('maxlength'=>"155", 'class'=>'form-control')))
            ->add('submit', SubmitType::class, array('label'=>$options['labelSubmit'], 'attr'=>array('class'=>'btn btn-primary')))
        ;
    } 
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Address',
            'labelSubmit'=>'label.submit'
        ));
    }
}