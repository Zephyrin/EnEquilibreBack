<?php

namespace App\Controller\Helpers;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This trait help for paginate header and add some usefull fonction like error.
 */
trait HelperController
{
    /**
     * Set headers for a paginate object.
     *
     * @param [type] $paginationArray paginate object.
     * @param [type] $parent the parent for the view.
     * @return void
     */
    public function setPaginateToView($paginationArray, $parent) {
        $view = $parent->view(
            $paginationArray[0]
        );
        $view->setHeader('X-Total-Count', $paginationArray[1]);
        $view->setHeader('X-Pagination-Count', $paginationArray[2]);
        $view->setHeader('X-Pagination-Page', $paginationArray[3]);
        $view->setHeader('X-Pagination-Limit', $paginationArray[4]);
        $view->setHeader('Access-Control-Expose-Headers'
            , 'X-Total-Count, X-Pagination-Count, X-Pagination-Page, X-Pagination-Limit');
        return $view;
    }

    /**
     * @param FormInterface $form
     * @param AbstractFOSRestController $controller
     * @param TranslatorInterface $translator.
     * @return bool|JsonResponse
     * @throws ExceptionInterface
     */
    public function validationError(FormInterface $form, AbstractFOSRestController $controller, TranslatorInterface $translator){
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => $translator->trans('error'),
                    'message' => $translator->trans('validation.error'),
                    'errors' => $controller->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        return true;
    }

    public function setCreatedByAndValidateToFalse(FormInterface $form, $connectUser = null) {
        $insertData = $form->getData();
        if ($connectUser == null) { $connectUser = $this->getUser(); }
        $insertData->setCreatedBy($connectUser);
        return $insertData;
    }
}