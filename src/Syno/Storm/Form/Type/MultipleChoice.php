<?php
namespace Syno\Storm\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultipleChoice extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'expanded' => true,
                'multiple' => true,
            ]
        );
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
