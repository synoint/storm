<?php

namespace Syno\Storm\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SingleChoiceSelect extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }
}
