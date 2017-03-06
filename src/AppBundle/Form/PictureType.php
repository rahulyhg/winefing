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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;


class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('picture', FileType::class, ['label'=>false, 'required' => false, 'data_class' => null, 'attr'=> ['accept'=>'image/*']])
            ->add('submit', SubmitType::class, ['label'=>'label.submit', 'attr'=>['style'=>'margin-top:20px', 'class'=>'btn btn-primary pull-left']])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
    }
}