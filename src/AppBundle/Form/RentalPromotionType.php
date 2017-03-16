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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class RentalPromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $options['user'];
        $builder
            ->add('startDate', DateType::class, ['label'=>false, 'format'=>'dd-MM-yyyy', 'widget' => 'single_text', 'html5'=> false, 'attr'=>['class'=>'input-sm form-control']])
            ->add('endDate', DateType::class, ['label'=>false, 'format'=>'dd-MM-yyyy', 'widget' => 'single_text', 'html5'=> false, 'attr'=>['class'=>'input-sm form-control']])
            ->add('reduction', NumberType::class, array('label'=> false,'scale'=>2,'attr' => array('min'=> 1.00, 'step'=>0.01, 'class'=>'form-control')))
            ->add('rentals', EntityType::class,  array(
                'class' => 'WinefingApiBundle:Rental',
                'query_builder' => function (EntityRepository $er) use ($userId){
                    return $er->createQueryBuilder('rental')
                        ->join('rental.property', 'property')
                        ->join('property.domain', 'domain')
                        ->join('domain.user', 'user')
                        ->where('user.id = :userId')
                        ->setParameter('userId', $userId)
                        ->orderBy('rental.name', 'ASC');
                },
                'choice_label' => 'name',
                'multiple' =>true,
                'label'=>false,
                 'attr'=>array('class'=>'selectpicker')))
            ->add('submit', SubmitType::class, array('attr'=>array('class'=>'btn btn-primary pull-right')))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\RentalPromotion',
            'user' => '',
        ));
    }
}