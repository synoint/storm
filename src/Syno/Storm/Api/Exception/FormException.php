<?php

namespace Syno\Storm\Api\Exception;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FormException extends HttpException
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * HttpFormException constructor.
     *
     * @param FormInterface $form
     * @param int $statusCode
     * @param string|null $message
     * @param \Exception|null $previous
     * @param array $headers
     * @param int|null $code
     */
    public function __construct(FormInterface $form, int $statusCode = 400, string $message = null, \Exception $previous = null, array $headers = [], ?int $code = 0)
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return FormErrorIterator
     */
    public function getErrors()
    {
        return $this->form->getErrors(true);
    }
}