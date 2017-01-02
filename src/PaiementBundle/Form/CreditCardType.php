<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 13:23
 */

namespace PaiementBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cardType', null, array('label'=>'label.card_type', 'attr'=> array('maxlength'=>"60", 'class'=>'form-control')))
            ->add('cardNumber', null, array('label'=>'label.card_number','attr'=> array('minlength'=>"11", 'maxlength'=>"19", 'required' => true, 'class'=>'form-control')))
            ->add('cardCode', null, array('label'=>'label.card_code','attr'=> array('minlength'=>"3", 'maxlength'=>"3", 'required' => true, 'class'=>'form-control')))
            ->add('cardDate', null, ['label'=>'label.card_expiration_date', 'attr'=>['class'=>'input-sm form-control']])
            ->add('submit', SubmitType::class, array('label'=>'label.submit', 'attr'=>array('class'=>'btn btn-primary pull-right')))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PaiementBundle\Entity\CreditCard',
        ));
    }
}