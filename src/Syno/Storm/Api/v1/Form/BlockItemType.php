<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Syno\Storm\Document;

class BlockItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', IntegerType::class)
            ->add('block', IntegerType::class)
            ->add('page', IntegerType::class)
            ->add('question', IntegerType::class)
            ->add('answer', IntegerType::class)
            ->add('randomize', IntegerType::class)
            ->add('weight', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => Document\BlockItem::class,
                'csrf_protection' => false,
            ]
        );
    }
}
