<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 13:23
 */

namespace PaiementBundle\Form;


use PaiementBundle\Entity\CardTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cardName', null, array('label'=>'label.name','attr'=> array('maxlength'=>"255", 'required' => true, 'class'=>'form-control','placeholder'=>'Audrey Carval'),'mapped'=>false))
            ->add('cardType', ChoiceType::class, array('choices' => array('CB' => CardTypeEnum::CB, 'MASTERCARD' => CardTypeEnum::Mastercard, 'VISA' => CardTypeEnum::VISA), 'label'=>'label.card_type', 'attr'=> array('maxlength'=>"60", 'class'=>'form-control')))
            ->add('cardNumber', null, array('label'=>'label.card_number','attr'=> array('placeholder'=>'•••• •••• •••• ••••', 'required' => true, 'class'=>'form-control')))
            ->add('cardCode', null, array('label'=>'label.card_code','attr'=> array('placeholder'=>'•••','minlength'=>"3", 'maxlength'=>"3", 'required' => true, 'class'=>'form-control')))
            ->add('cardDate', null, ['label'=>'label.card_expiration_date', 'attr'=>['placeholder'=>'••/••••','class'=>'input-sm form-control']])
            ->add('save', CheckboxType::class, ['label'=>false, 'mapped'=>false, 'required'=>false])
            ->add('cgv', CheckboxType::class, ['label'=>false, 'mapped'=>false])
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