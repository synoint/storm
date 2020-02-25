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
            'attr' => ['class' => 'custom-control custom-radio'],
            'choice_attr' => function() {
                return ['class' => 'custom-control-input form-check-input'];
            },
            'choice_value' => function(?Document\Answer $choice) {
                return $choice ? $choice->getAnswerId() : '';
            },
            'choice_label' => function (?Document\Answer $choice) {
                return $choice ? $choice->getLabel() : '';
            },
            'label_attr' => ['class' => 'custom-control-label']
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}