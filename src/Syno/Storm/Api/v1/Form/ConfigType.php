<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Syno\Storm\Document;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('privacyConsentEnabled', CheckboxType::class, ['required' => false])
            ->add('theme', HiddenType::class, ['required' => false, 'empty_data' => Document\Config::DEFAULT_THEME])
            ->add('cintDemandApiKey', HiddenType::class, ['required' => false])
            ->add('profilingSurveyCallbackUrl', HiddenType::class, ['required' => false])
            ->add('backButtonEnabled', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class'      => Document\Config::class,
           'csrf_protection' => false
        ]);
    }
}
