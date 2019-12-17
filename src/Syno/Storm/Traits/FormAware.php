<?php
namespace Syno\Storm\Traits;

use Symfony\Component\Form\FormInterface;

trait FormAware {

    /**
     * List all errors of a given bound form.
     *
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getFormErrors(FormInterface $form)
    {
        $errors = array();

        // Global
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()][] = $error->getMessage();
        }

        return $errors;
    }
}
