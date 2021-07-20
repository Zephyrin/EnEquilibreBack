<?php

namespace App\Controller\Helpers;

use Behat\Behat\Definition\Translator\Translator;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;

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
    public function setPaginateToView($paginationArray, $parent)
    {
        $view = $parent->view(
            $paginationArray[0]
        );
        $view->setHeader('X-Total-Count', $paginationArray[1]);
        $view->setHeader('X-Pagination-Count', $paginationArray[2]);
        $view->setHeader('X-Pagination-Page', $paginationArray[3]);
        $view->setHeader('X-Pagination-Limit', $paginationArray[4]);
        $view->setHeader(
            'Access-Control-Expose-Headers',
            'X-Total-Count, X-Pagination-Count, X-Pagination-Page, X-Pagination-Limit'
        );
        return $view;
    }

    /**
     * @param FormInterface $form
     * @param AbstractFOSRestController $controller
     * @param TranslatorInterface $translator.
     * @return bool|JsonResponse
     * @throws ExceptionInterface
     */
    public function validationError(
        FormInterface $form,
        AbstractFOSRestController $controller,
        TranslatorInterface $translator
    ) {
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

    /**
     * Return the data of the JSON or a validation error.
     *
     * @param Request $request
     * @param boolean $assoc
     * @return mixed|JsonResponse
     */
    public function getDataFromJson(
        Request $request,
        bool $assoc,
        TranslatorInterface $translator
    ) {
        $data = json_decode($request->getContent(), $assoc);
        if ($data === null || count($data) === 0) {
            return new JsonResponse(
                [
                    'status' => $translator->trans('error'),
                    'message' => $translator->trans('validation.error'),
                    'errors' => $translator->trans('json.empty.error'),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        return $data;
    }

    public function createError(
        FormInterface $form,
        AbstractFOSRestController $controller,
        TranslatorInterface $translator,
        string $messageError
    ) {
        $error = $controller->formErrorSerializer->normalize($form);

        return new JsonResponse(
            [
                'status' => $translator->trans('error'),
                'message' => $translator->trans('validation.error'),
                'errors' => $error
            ],
            JsonResponse::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function createConflictError(
        string $message,
        TranslatorInterface $translator
    ) {
        return new JsonResponse(
            [
                'status' => $translator->trans('error'),
                'message' => $translator->trans('conflict.error'),
                'errors' => $translator->trans($message)
            ],
            JsonResponse::HTTP_CONFLICT
        );
    }

    public function formatErrorManageImage(
        array $data,
        Exception $e,
        TranslatorInterface $translator
    ) {
        $children = [];

        foreach ($data as $key => $value) {

            if ($key === "image") {
                $error["errors"] = [$translator->trans($e->getMessage())];
                $children[$key] = $error;
            } else {
                $children[$key] = [];
            }
        }
        $errors = [];
        $tmp["children"] = $children;
        array_push($errors, $tmp);


        return new JsonResponse(
            [
                'status' => $translator->trans('error'),
                'message' => $translator->trans('validation.error'),
                'errors' => $errors
            ],
            JsonResponse::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function validationErrorWithChild(
        FormInterface $form,
        AbstractFOSRestController $controller,
        ?Response $response,
        string $field,
        TranslatorInterface $translator
    ) {
        $code = 200;
        if ($response != null)
            $code = $response->getStatusCode();
        if (false === $form->isValid() || ($code != 201 && $code != 200 && $code != 204)) {
            $data = [
                'status' => $translator->trans('error'),
                'message' => $translator->trans('validation.error'),
                'errors' => $controller->formErrorSerializer->normalize($form),
            ];
            if ($response != null) {
                $errors = json_decode($response->getContent(), true);
                if (isset($errors["errors"][0])) {
                    if (isset($data["errors"][0]["children"])) {
                        $data["errors"][0]["children"][$field] = $errors["errors"][0];
                    }
                }
            }
            return new JsonResponse($data, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        return true;
    }

    public function createOrUpdateMediaObject(?array &$data, string $name, bool $clearData = false)
    {
        $response_json = null;
        $response = null;
        if ($data == null)
            return $response;
        if (isset($data[$name]) && isset($data[$name][$this->id])) {
            $id = $data[$name][$this->id];
            unset($data[$name][$this->id]);
            $response = $this->forward(
                "App\Controller\MediaObjectController::putOrPatch",
                ["data" => $data[$name], "id" => $id, "clearMissing" => $clearData]
            );
            $data[$name] = $id;
        } else if (isset($data[$name]) && !(gettype($data[$name]) === "integer")) {
            $response = $this->forward(
                "App\Controller\MediaObjectController::post",
                ['data' => $data[$name]]
            );
            if ($response->getStatusCode() == 201) {
                $response_json = json_decode($response->getContent(), true);
                if (isset($response_json[$this->id])) {
                    $data[$name] = $response_json[$this->id];
                }
            }
        }
        return $response;
    }
}
