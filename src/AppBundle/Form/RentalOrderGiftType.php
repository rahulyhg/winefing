<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class RentalOrderGiftType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('signature', null, array('label'=> 'label.signature', 'attr'=> array('maxlength'=>"60", 'required'=> false, 'class'=>'form-control')))
            ->add('message', TextareaType::class, array('label'=> 'label.message','attr'=> array('maxlength'=>"255", 'required'=> false, 'class'=>'form-control')))
            ->add('agree', CheckboxType::class, array('label'=>false, 'mapped'=>false))
            ->add('submit', SubmitType::class, array('label'=>false))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\RentalOrderGift'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'winefing_apibundle_rentalordergift';
    }


}
