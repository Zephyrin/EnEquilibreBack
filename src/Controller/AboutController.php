<?php

namespace App\Controller;

use App\Entity\About;
use App\Form\AboutType;
use App\Repository\AboutRepository;
use App\Controller\Helpers\HelperController;
use App\Controller\Helpers\TranslatableHelperController;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JsonException;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AboutController
 * @package App\Controller
 *
 * @Route("api")
 * @SWG\Tag(
 *     name="About"
 * )
 * 
 */
class AboutController extends AbstractFOSRestController
{
    use HelperController;

    use TranslatableHelperController;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var AboutRepository
     */
    private $aboutRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $separator = "separator";

    private $background = "background";

    private $id = "id";

    private $about = "about";
    private $comment = "comment";

    public function __construct(
        EntityManagerInterface $entityManager,
        AboutRepository $aboutRepository,
        FormErrorSerializer $formErrorSerializer,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->aboutRepository = $aboutRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    /**
     * Create a new About page only if user is at least commercant.
     * 
     * @Route("/{_locale}/about",
     *  name="api_about_post",
     *  methods={"POST"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=201,
     *      description="Successful operation with the new value insert.",
     *      @SWG\Schema(
     *       ref=@Model(type=About::class)
     *     )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct.<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=401,
     *     description="You are not allow to create a about page for an another user."
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON About",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=About::class)
     *     ),
     *     description="The JSon About page. Only used to create the about page at initialisation."
     *    )
     * )
     *
     * @param Request $request
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        /* Check if there is another about page. */
        try {
            $about = $this->getAbout();
            if ($about instanceof JsonResponse)
                return $about;
            else
                return $this->createConflictError("about.already.exists", $this->translator);
        } catch (NotFoundHttpException $e) {
            // Ok, this is what we need to create a new about page.
        }
        $data = $this->getDataFromJson($request, true, $this->translator);

        if ($data instanceof JsonResponse)
            return $data;
        $this->setLang($data, $this->about);
        $this->setLang($data, $this->comment);
        $responseSeparator = $this->createOrUpdateMediaObject($data, $this->separator);
        $responseBackground = $this->createOrUpdateMediaObject($data, $this->background);

        $form = $this->createForm(AboutType::class, new About());
        $form->submit($data, false);

        $validation =
            $this->validationErrorWithChild(
                $form,
                $this,
                $responseSeparator,
                $this->separator,
                $this->translator
            );
        if ($validation instanceof JsonResponse) {
            return $validation;
        }
        $validation = $this->validationErrorWithChild(
            $form,
            $this,
            $responseBackground,
            $this->background,
            $this->translator
        );
        if ($validation instanceof JsonResponse) {
            return $validation;
        }

        $insertData = $form->getData();
        $this->translate($insertData, $this->about, $this->entityManager);
        $this->translate($insertData, $this->comment, $this->entityManager);

        $this->entityManager->persist($insertData);

        $this->entityManager->flush();
        return  $this->view($insertData, Response::HTTP_CREATED);
    }

    /**
     * Expose the About page information.
     *
     * @Route("/{_locale}/about",
     *  name="api_about_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get the About page",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the About page",
     *     @SWG\Schema(ref=@Model(type=About::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The About page does not exists yet"
     * )
     *
     * @return View
     */
    public function getAction()
    {
        $about = $this->getAbout();
        if ($about instanceof JsonResponse)
            return $about;
        return $this->view($about);
    }

    /**
     * Expose the About page information with all languages for merchant/admin edition.
     *
     * @Route("/about",
     *  name="api_about_merchant_get",
     *  methods={"GET"})
     * 
     * @SWG\Get(
     *     summary="Get the About page",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the About page",
     *     @SWG\Schema(ref=@Model(type=About::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The About page does not exists yet"
     * )
     *
     * @return View
     */
    public function getActionMerchant()
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");

        $about = $this->getAbout();
        if ($about instanceof JsonResponse)
            return $about;
        /** @var Gedmo\Translatable\Entity\Translation */
        $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $array = $this->createTranslatableArray();
        $this->addTranslatableVar(
            $array,
            $repository->findTranslations($about)
        );
        if ($about->getBackground() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($about->getBackground()),
                $this->background
            );
        if ($about->getSeparator() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($about->getSeparator()),
                $this->separator
            );
        $about->setTranslations($array);

        return $this->view($about);
    }

    /**
     * Update a About page.
     * 
     * @Route("/{_locale}/about",
     *  name="api_about_put",
     *  methods={"PUT"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Put(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=204,
     *      description="Successful operation"
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON About",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=About::class)
     *     ),
     *     description="The JSon About"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the About"
     *    )
     * )
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function putAction(Request $request)
    {
        return $this->putOrPatch($request, true);
    }

    /**
     * Update a part of a About page
     *
     * All missing attribute will not be update.
     *
     * @Route("/{_locale}/about",
     *  name="api_about_patch",
     *  methods={"PATCH"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Patch(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=204,
     *      description="Successful operation"
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Response(
     *     response=404,
     *     description="The About page is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON About",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(ref=@Model(type=About::class)),
     *     description="A part of a JSon About"
     *    )
     * )
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function patchAction(Request $request)
    {
        return $this->putOrPatch($request, false);
    }

    private function putOrPatch(Request $request, bool $clearData)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        $existingAbout = $this->getAbout();
        if ($existingAbout instanceof JsonResponse)
            return $existingAbout;
        $form = $this->createForm(AboutType::class, $existingAbout);
        if (count($this->aboutRepository->findAll()) > 1) {
            return $this->createError($form, $this, $this->translator, "too.many.about.page");
        }
        $data = $this->getDataFromJson($request, true, $this->translator);
        if ($data instanceof JSonResponse) {
            return $data;
        }

        $this->setLang($data, $this->about);
        $this->setLang($data, $this->comment);
        /* $separator = $existingAbout->getSeparator();
        $background = $existingAbout->getBackground(); */
        $responseSeparator = $this->createOrUpdateMediaObject($data, $this->separator, $clearData);
        $responseBackground = $this->createOrUpdateMediaObject($data, $this->background, $clearData);

        $form->submit($data, $clearData);

        $validation = $this->validationErrorWithChild(
            $form,
            $this,
            $responseSeparator,
            $this->separator,
            $this->translator
        );
        if ($validation instanceof JsonResponse)
            return $validation;
        $validation = $this->validationErrorWithChild(
            $form,
            $this,
            $responseBackground,
            $this->background,
            $this->translator
        );
        if ($validation instanceof JsonResponse) {
            return $validation;
        }
        $insertData = $form->getData();
        $this->translate($insertData, $this->about, $this->entityManager, $clearData);
        $this->translate($insertData, $this->comment, $this->entityManager, $clearData);

        /* I keep picture for later use. I get a new interface to delete it if the user really want */
        /*         if (($separator != null
                && $existingAbout->getSeparator() == null
                && $clearData)
            || ($separator != null
                && $existingAbout->getSeparator() != null
                && $separator->getId() != $existingAbout->getSeparator()->getId())
        ) {
            $responseDelete =  $this->forward(
                "App\Controller\MediaObjectController::deleteAction",
                ['id' => $separator->getId()]
            );
            if ($responseDelete->getStatusCode() != 204 && $responseDelete->getStatusCode() != 404)
                return $responseDelete;
        }
        if (($background != null
                && $existingAbout->getBackground() == null
                && $clearData)
            || ($background != null
                && $existingAbout->getBackground() != null
                && $background->getId() != $existingAbout->getBackground()->getId())
        ) {
            $responseDelete =  $this->forward(
                "App\Controller\MediaObjectController::deleteAction",
                ['id' => $background->getId()]
            );
            if ($responseDelete->getStatusCode() != 204 && $responseDelete->getStatusCode() != 404)
                return $responseDelete;
        } */
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete the about page.
     * 
     * You should know what your are doing ! Cannot be reverse.
     *
     * @Route("/{_locale}/about",
     *  name="api_about_delete",
     *  methods={"DELETE"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Delete()
     * @SWG\Response(
     *     response=204,
     *     description="The about page is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The about page doesnot exists."
     * )
     *
     * 
     * @return View|JsonResponse
     */
    public function deleteAction()
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $about = $this->getAbout();
        if ($about instanceof JsonResponse)
            return $about;

        $this->entityManager->remove($about);
        $this->entityManager->flush();
        /* I keep picture for later use. Never know if the user want it. */
        /* if ($about->getSeparator() != null) {
            $responseDelete =  $this->forward(
                "App\Controller\MediaObjectController::deleteAction",
                ['id' => $about->getSeparator()->getId()]
            );
            if ($responseDelete->getStatusCode() != 204 && $responseDelete->getStatusCode() != 404)
                return $responseDelete;
        }
        if ($about->getBackground() != null) {
            $responseDelete =  $this->forward(
                "App\Controller\MediaObjectController::deleteAction",
                ['id' => $about->getBackground()->getId()]
            );
            if ($responseDelete->getStatusCode() != 204 && $responseDelete->getStatusCode() != 404)
                return $responseDelete;
        } */
        return $this->view(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    private function getAbout()
    {
        $abouts = $this->aboutRepository->findAll();
        if (count($abouts) > 1) {
            return $this->createConflictError("too.many.about.page", $this->translator);
        } else if (count($abouts) === 0) {
            throw new NotFoundHttpException();
        }
        return $abouts[0];
    }
}
