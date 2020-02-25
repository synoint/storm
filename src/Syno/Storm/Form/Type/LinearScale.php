<?php

namespace Syno\Storm\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Syno\Storm\Document;

class LinearScale extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expanded' => true,
            'choice_attr' => function() {
                return ['class' => 'custom-control-input'];
            },
            'choice_value' => function(?Document\Answer $choice) {
                return $choice ? $choice->getAnswerId() : '';
            },
            'choice_label' => function (?Document\Answer $choice) {
                return $choice ? $choice->getLabel() : '';
            }
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}