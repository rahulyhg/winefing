<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 13:23
 */

namespace AppBundle\Form;


namespace AppBundle\Form;
use AppBundle\Form\SubscriptionTrType;
use Winefing\ApiBundle\Entity\SubscriptionFormatEnum;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',  HiddenType::class, array('required' => false))
            ->add('activated',  CheckboxType::class, array('required' => false))
            ->add('code')
            ->add('subscriptionTrs', CollectionType::class, array(
            'entry_type' => SubscriptionTrType::class))
            ->add('format', ChoiceType::class, array('choices' => array(SubscriptionFormatEnum::Sms => SubscriptionFormatEnum::Sms,
                SubscriptionFormatEnum::Email=>SubscriptionFormatEnum::Email)))
            ->add('userGroup', ChoiceType::class, array('choices' => array(UserGroupEnum::Host => UserGroupEnum::Host,
                UserGroupEnum::User=>UserGroupEnum::User)));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Subscription'
        ));
    }
}