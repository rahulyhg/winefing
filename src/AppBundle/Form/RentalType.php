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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Winefing\ApiBundle\Entity\RentalCategoryEnum;


class RentalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $options["user"];
        $builder
            ->add('name', null, array('label'=> false, 'attr'=> array('maxlength'=>"60", 'required'=> true)))
            ->add('description', TextareaType::class, array('required' => false,'attr'=> array('maxlength'=>"500", 'style' => 'height:200px')))
            ->add('price', MoneyType::class, array('label'=> false, 'currency'=>'','scale'=>2,'attr' => array('min'=> 1, 'step'=>0.01)))
            ->add('peopleNumber', IntegerType::class, array('label'=> false, 'attr' => array('min'=> 1)))
            ->add('minimumRentalPeriod', IntegerType::class, array('label'=> false, 'attr'=> array('min'=> 1)))
            ->add('rentalCategory', ChoiceType::class, array('label'=> false,'choices' => array('label.room'=>RentalCategoryEnum::Room,'label.whole_housing'=> RentalCategoryEnum::WholeHousing)))
            ->add('property', EntityType::class,  array(
                'label'=> false,
                'class' => 'WinefingApiBundle:Property',
                'choice_label' => 'name',
                'placeholder' => 'label.new_property',
                'query_builder' => function (EntityRepository $er) use ($userId){
                    return $er->createQueryBuilder('property')
                        ->join('property.domain', 'domain')
                        ->join('domain.user', 'user')
                        ->where('user.id = :userId')
                        ->setParameter('userId', $userId)
                        ->orderBy('property.name', 'ASC');
                },
                'required' => false))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Rental',
            'user' => ''
        ));
    }
}