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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Winefing\ApiBundle\Repository\UserRepository;
use Winefing\ApiBundle\Entity\UserGroupEnum;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',  HiddenType::class, array(
                'required' => false))
            ->add('user', EntityType::class,  array(
                'class' => 'WinefingApiBundle:User',
                'choice_label' => 'fullName',
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->where('user.roles like :blog or user.roles like :managment')
                        ->setParameter('blog', '%'.UserGroupEnum::Blog.'%')
                        ->setParameter('managment', '%'.UserGroupEnum::Managment.'%');
                    }
                ))
            ->add('picture', FileType::class, ['required' => false, 'data_class' => null])
            ->add('articleCategories', EntityType::class,  array(
                'class' => 'WinefingApiBundle:ArticleCategory',
                'choice_label' => 'title', 'multiple' =>true))
            ->add('tags', EntityType::class,  array('multiple'=>true,'class' => 'WinefingApiBundle:Tag','attr'=> array('class'=> 'selectpicker'),
                'choice_label' => function ($tag) use ($options) {
                    return $tag->getDisplayName($options['language']);
                }))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\Article',
            'language'=>'fr'
        ));
    }
}