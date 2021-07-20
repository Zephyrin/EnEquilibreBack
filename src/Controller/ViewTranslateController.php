<?php

namespace App\Controller;

use App\Entity\ViewTranslate;
use App\Repository\ViewTranslateRepository;
use App\Form\ViewTranslateType;
use App\Controller\Helpers\HelperController;
use App\Controller\Helpers\TranslatableHelperController;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ViewTranslateController
 *
 * @Route("/api")
 * @SWG\Tag(
 *     name="ViewTranslate"
 * )
 */
class ViewTranslateController extends AbstractFOSRestController
{
    use TranslatableHelperController;

    use HelperController;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ViewTranslateRepository
     */
    private $viewTranslateRepository;
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The field name use for translation.
     */
    private string $translate = "translate";

    public function __construct(
        EntityManagerInterface $entityManager,
        ViewTranslateRepository $viewTranslateRepository,
        FormErrorSerializer $formErrorSerializer,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->viewTranslateRepository = $viewTranslateRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    /**
     * Upload a new translation for the view.
     * 
     * Post into {_locale} to get the error.
     * 
     * @Route("/{_locale}/viewtranslate",
     *  name="api_viewtranslate_post",
     *  methods={"POST"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation with the new value insert.",
     *      @SWG\Schema(
     *       ref=@Model(type=ViewTranslate::class)
     *      )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allow to create a new translate."
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON ViewTranslate",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ViewTranslate::class)
     *     ),
     *     description="The JSon ViewTranslate object."
     *    )
     *
     * )
     *
     * @param Request $request
     * @return View|JsonResponse
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function postAction(Request $request)
    {
        $data = $this->getDataFromJson($request, true, $this->translator);
        if ($data instanceof JsonResponse)
            return $data;
        return $this->post($data);
    }

    public function post($data)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        $form = $this->createForm(ViewTranslateType::class, new ViewTranslate());
        if (is_array($data)) {
            $this->setLang($data, $this->translate);
        } else {
            return $this->createError($form, $this, $this->translator, "invalid.json");
        }

        $form = $form->submit($data, false);
        $validation = $this->validationError($form, $this, $this->translator);
        if ($validation instanceof JsonResponse)
            return $validation;

        $viewTranslate = $form->getData();
        $this->translate($viewTranslate, $this->translate, $this->entityManager);

        $this->entityManager->persist($viewTranslate);
        $this->entityManager->flush();
        return  $this->view(
            $viewTranslate,
            Response::HTTP_CREATED
        );
    }

    /**
     * Expose a view translate.
     * 
     * @Route("/{_locale}/viewtranslate/{key}",
     *  name="api_viewtranslate_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "key": "[a-zA-Z.-]+"
     * })
     *
     * @SWG\Get(
     *     summary="Get a view translate based on its Key.",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the view translate Entity based on Key.",
     *     @SWG\Schema(ref=@Model(type=ViewTranslate::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The view translate based on Key does not exists."
     * )
     *
     * @SWG\Parameter(
     *     name="key",
     *     in="path",
     *     type="string",
     *     description="The Key used to find the information about view translate."
     * )
     *
     *
     * @param string $key
     * @return View
     */
    public function getAction(string $key)
    {
        return $this->view(
            $this->findViewTranslateByKey($key)
        );
    }

    /**
     * Expose a view translate with its all translate.
     * 
     * @Route("/viewtranslate/{key}",
     *  name="api_viewtranslate_merchant_get",
     *  methods={"GET"},
     *  requirements={
     *      "key": "[a-zA-Z.-]+"
     * })
     *
     * @SWG\Get(
     *     summary="Get a view translate based on its Key.",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the view translate Entity based on Key.",
     *     @SWG\Schema(ref=@Model(type=ViewTranslate::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The view translate based on Key does not exists."
     * )
     *
     * @SWG\Parameter(
     *     name="key",
     *     in="path",
     *     type="string",
     *     description="The Key used to find the information about view translate."
     * )
     *
     *
     * @param string $key
     * @return View
     */
    public function getActionMerchant(string $key)
    {
        $viewTranslate = $this->findViewTranslateByKey($key);
        if ($viewTranslate instanceof JsonResponse)
            return $viewTranslate;
        $this->addTranslation($viewTranslate);

        return $this->view($viewTranslate);
    }

    /**
     * Expose all ViewTranslates and their informations based on locale.
     * 
     * @Route("/{_locale}/viewtranslates",
     *  name="api_ViewTranslate_gets",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get all ViewTranslates",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all ViewTranslates and their informations based on locale.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=ViewTranslate::class))
     *     )
     * )
     *
     * @param Request $request
     * @return View
     */
    public function cgetAction(Request $request)
    {
        return $this->view(
            $this->viewTranslateRepository->findAll()
        );
    }

    /**
     * Expose all ViewTranslates and their informations based on locale for merchant.
     * 
     * @Route("/viewtranslates",
     *  name="api_ViewTranslate_merchant_gets",
     *  methods={"GET"}
     *  )
     * 
     * @SWG\Get(
     *     summary="Get all ViewTranslates",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all ViewTranslates and their informations based on locale.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=ViewTranslate::class))
     *     )
     * )
     *
     * @param Request $request
     * @return View
     */
    public function cgetActionMerchant(Request $request)
    {
        $viewTranslates = $this->viewTranslateRepository->findAll();
        foreach ($viewTranslates as $viewTranslate) {
            $this->addTranslation($viewTranslate);
        }
        return $this->view($viewTranslates);
    }

    private function addTranslation(ViewTranslate &$viewTranslate)
    {
        /** @var Gedmo\Translatable\Entity\Translation */
        $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $array = $this->createTranslatableArray();
        $this->addTranslatableVar(
            $array,
            $repository->findTranslations($viewTranslate),
            $this->translate
        );
        $viewTranslate->setTranslations($array);
    }

    /**
     * Update a ViewTranslate entirely.
     *
     * @Route("/{_locale}/viewtranslate/{key}",
     *  name="api_viewtranslate_put",
     *  methods={"PUT"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "key": "[a-zA-Z.-]+"
     * })
     * 
     * @SWG\Put(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=204,
     *      description="Successful operation."
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allowed to update a ViewTranslate."
     *    ),
     *    @SWG\Response(
     *     response=404,
     *     description="The ViewTranslate based on Key is not found."
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON ViewTranslate.",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ViewTranslate::class)
     *     ),
     *     description="The JSon ViewTranslate."
     *    ),
     *    @SWG\Parameter(
     *     name="key",
     *     in="path",
     *     type="string",
     *     description="The Key used to find the ViewTranslate."
     *    )
     * )
     *
     * @param Request $request
     * @param string $key of the ViewTranslate to update.
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function putAction(Request $request, string $key)
    {
        return $this->putOrPatch($this->getDataFromJson($request, true, $this->translator), $key, true);
    }

    /**
     * Update a part of a ViewTranslate.
     * 
     * @Route("/{_locale}/viewtranslate/{key}",
     *  name="api_viewtranslate_patch",
     *  methods={"PATCH"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "key": "[a-zA-Z.-]+"
     * })
     * @SWG\Patch(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=204,
     *      description="Successful operation."
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct.<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=404,
     *     description="The ViewTranslate based on Key is not found."
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allowed to update a ViewTranslate."
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON ViewTranslate",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=ViewTranslate::class)
     *     ),
     *     description="The JSon ViewTranslate."
     *    ),
     *    @SWG\Parameter(
     *     name="key",
     *     in="path",
     *     type="string",
     *     description="The Key used to find the ViewTranslate."
     *    )
     * )
     *
     * @param Request $request
     * @param string $key of the ViewTranslate to update
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function patchAction(Request $request, string $key)
    {
        return $this->putOrPatch($this->getDataFromJson($request, true, $this->translator), $key, false);
    }

    /**
     * Delete an ViewTranslate with the key.
     *
     * @Route("/{_locale}/viewtranslate/{key}",
     *  name="api_viewtranslate_delete",
     *  methods={"DELETE"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Delete(
     *     summary="Delete an ViewTranslate based on Key."
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The ViewTranslate is correctly delete.",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The ViewTranslate based on Key is not found."
     * )
     * 
     * @SWG\Response(
     *      response=403,
     *      description="You are not authorized to delete this ViewTranslate."
     * )

     * @SWG\Parameter(
     *     name="key",
     *     in="path",
     *     type="string",
     *     description="The Key used to find the ViewTranslate."
     * )
     * @param string $Key
     * @throws Exception
     * @return View
     */
    public function deleteAction(string $key)
    {
        $viewTranslate = $this->findViewTranslateByKey($key);
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        $this->entityManager->remove($viewTranslate);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $key
     *
     * @return ViewTranslate
     * @throws NotFoundHttpException
     */
    private function findViewTranslateByKey(string $key)
    {
        $existingViewTranslate = $this->viewTranslateRepository->find($key);
        if (null === $existingViewTranslate) {
            throw new NotFoundHttpException();
        }
        return $existingViewTranslate;
    }

    /**
     * @param array $request
     * @param string $key
     * @param bool $clearMissing
     * @return View|JsonResponse
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function putOrPatch(array $data, string $key, bool $clearMissing)
    {
        $existingViewTranslateField = $this->findViewTranslateByKey($key);
        $form = $this->createForm(ViewTranslateType::class, $existingViewTranslateField);
        if ($data instanceof JsonResponse)
            return $data;
        $this->setLang($data, $this->translate);
        $form->submit($data, $clearMissing);
        $validation = $this->validationError($form, $this, $this->translator);
        if ($validation instanceof JsonResponse)
            return $validation;
        $viewTranslate = $form->getData();
        $this->translate($viewTranslate, $this->translate, $this->entityManager, $clearMissing);

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
