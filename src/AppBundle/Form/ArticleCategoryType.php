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
use Winefing\ApiBundle\Entity\CharacteristicCategory;
use AppBundle\Form\CharacteristicCategoryTrType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ArticleCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',  HiddenType::class, array(
                'required' => false))
            ->add('description', TextareaType::class, ['required' => false])
            ->add('categoryPere', EntityType::class,  array(
                'required' => false,
                'class' => 'WinefingApiBundle:ArticleCategory',
                'choice_label' => 'title',
                'placeholder' => ''))
            ->add('articleCategoryTrs', CollectionType::class, array(
                'entry_type' => ArticleCategoryTrType::class))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Winefing\ApiBundle\Entity\ArticleCategory',
        ));
    }
}