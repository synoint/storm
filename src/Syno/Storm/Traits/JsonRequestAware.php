<?php
namespace Syno\Storm\Traits;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait JsonRequestAware {

    /**
     * @param Request $request
     *
     * @return mixed
     *
     * @throws HttpException
     */
    protected function getJson(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(400, 'Invalid json');
        }

        return $data;
    }
}
