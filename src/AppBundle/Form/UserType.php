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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, array('attr'=> array('maxlength'=>"60")))
            ->add('lastName', null, array('attr'=> array('maxlength'=>"60")))
            ->add('birthDate', DateType::class, ['label'=>false, 'format'=>'dd-MM-yyyy', 'widget' => 'single_text', 'html5'=> false, 'attr'=>['class'=>'input-sm form-control']])
            ->add('phoneNumber', null)
            ->add('email', EmailType::class)
            ->add('description', TextareaType::class, array('required' => false, 'attr'=> array('maxlength'=>"500", 'style' => 'height:250px')))
            ->add('sex', ChoiceType::class,  array(
                'choices' => array('label.female' => 'F', 'label.male' => 'M'), 'required'    => false, 'empty_data'  => null));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\User',
        ));
    }
}