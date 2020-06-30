<?php

namespace App\Controller;

use App\Entity\Home;
use App\Form\HomeType;
use App\Repository\HomeRepository;
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
 * Class HomeController
 * @package App\Controller
 *
 * @Route("api")
 * @SWG\Tag(
 *     name="Home"
 * )
 * 
 */
class HomeController extends AbstractFOSRestController
{
    use HelperController;

    use TranslatableHelperController;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var HomeRepository
     */
    private $homeRepository;

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

    public function __construct(
        EntityManagerInterface $entityManager,
        HomeRepository $homeRepository,
        FormErrorSerializer $formErrorSerializer,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->homeRepository = $homeRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    /**
     * Create a new Home page only if user is at least commercant.
     * 
     * @Route("/{_locale}/home",
     *  name="api_home_post",
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
     *       ref=@Model(type=Home::class)
     *     )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct.<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=401,
     *     description="You are not allow to create a home page for an another user."
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Home",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Home::class)
     *     ),
     *     description="The JSon Home page. Only used to create the home page at initialisation."
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
        /* Check if there is another home page. */
        try {
            $home = $this->getHome();
            if ($home instanceof JsonResponse)
                return $home;
            else
                return $this->createConflictError("home.already.exists", $this->translator);
        } catch (NotFoundHttpException $e) {
            // Ok, this is what we need to create a new home page.
        }
        $data = $this->getDataFromJson($request, true, $this->translator);
        if ($data instanceof JsonResponse)
            return $data;
        $responseSeparator = $this->createOrUpdateMediaObject($data, $this->separator);
        $responseBackground = $this->createOrUpdateMediaObject($data, $this->background);

        $form = $this->createForm(HomeType::class, new Home());
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

        $this->entityManager->persist($insertData);

        $this->entityManager->flush();
        return  $this->view($insertData, Response::HTTP_CREATED);
    }

    /**
     * Expose the Home page information.
     *
     * @Route("/{_locale}/home",
     *  name="api_home_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get the Home page",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Home page",
     *     @SWG\Schema(ref=@Model(type=Home::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Home page does not exists yet"
     * )
     *
     * @return View
     */
    public function getAction()
    {
        $home = $this->getHome();
        if ($home instanceof JsonResponse)
            return $home;
        return $this->view($home);
    }

    /**
     * Expose the Home page information with all languages for merchant/admin edition.
     *
     * @Route("/home",
     *  name="api_home_merchant_get",
     *  methods={"GET"})
     * 
     * @SWG\Get(
     *     summary="Get the Home page",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Home page",
     *     @SWG\Schema(ref=@Model(type=Home::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Home page does not exists yet"
     * )
     *
     * @return View
     */
    public function getActionMerchant()
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");

        $home = $this->getHome();
        if ($home instanceof JsonResponse)
            return $home;
        $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $array = $this->createTranslatableArray();
        if ($home->getBackground() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($home->getBackground()),
                $this->background
            );
        if ($home->getSeparator() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($home->getSeparator()),
                $this->separator
            );
        $home->setTranslations($array);

        return $this->view($home);
    }

    /**
     * Update a Home page.
     * 
     * @Route("/{_locale}/home",
     *  name="api_home_put",
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
     *     name="The full JSON Home",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Home::class)
     *     ),
     *     description="The JSon Home"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Home"
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
     * Update a part of a Home page
     *
     * All missing attribute will not be update.
     *
     * @Route("/{_locale}/home",
     *  name="api_home_patch",
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
     *     description="The Home page is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON Home",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(ref=@Model(type=Home::class)),
     *     description="A part of a JSon Home"
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

    private function createOrUpdateMediaObject(?array &$data, string $name, bool $clearData = false)
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

    private function putOrPatch(Request $request, bool $clearData)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        $existingHome = $this->getHome();
        if ($existingHome instanceof JsonResponse)
            return $existingHome;
        $form = $this->createForm(HomeType::class, $existingHome);
        if (count($this->homeRepository->findAll()) > 1) {
            return $this->createError($form, $this, $this->translator, "too.many.home.page");
        }
        $data = $this->getDataFromJson($request, true, $this->translator);
        if ($data instanceof JSonResponse) {
            return $data;
        }
        $separator = $existingHome->getSeparator();
        $background = $existingHome->getBackground();
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

        if (($separator != null
                && $existingHome->getSeparator() == null
                && $clearData)
            || ($separator != null
                && $existingHome->getSeparator() != null
                && $separator->getId() != $existingHome->getSeparator()->getId())
        ) {
            $responseDelete =  $this->forward(
                "App\Controller\MediaObjectController::deleteAction",
                ['id' => $separator->getId()]
            );
            if ($responseDelete->getStatusCode() != 204 && $responseDelete->getStatusCode() != 404)
                return $responseDelete;
        }
        if (($background != null
                && $existingHome->getBackground() == null
                && $clearData)
            || ($background != null
                && $existingHome->getBackground() != null
                && $background->getId() != $existingHome->getBackground()->getId())
        ) {
            $responseDelete =  $this->forward(
                "App\Controller\MediaObjectController::deleteAction",
                ['id' => $background->getId()]
            );
            if ($responseDelete->getStatusCode() != 204 && $responseDelete->getStatusCode() != 404)
                return $responseDelete;
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete the home page.
     * 
     * You should know what your are doing ! Cannot be reverse.
     *
     * @Route("/{_locale}/home",
     *  name="api_home_delete",
     *  methods={"DELETE"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Delete()
     * @SWG\Response(
     *     response=204,
     *     description="The home page is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The home page doesnot exists."
     * )
     *
     * 
     * @return View|JsonResponse
     */
    public function deleteAction()
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $home = $this->getHome();
        if ($home instanceof JsonResponse)
            return $home;

        $this->entityManager->remove($home);
        $this->entityManager->flush();
        if ($home->getSeparator() != null) {
            $responseDelete =  $this->forward(
                "App\Controller\MediaObjectController::deleteAction",
                ['id' => $home->getSeparator()->getId()]
            );
            if ($responseDelete->getStatusCode() != 204 && $responseDelete->getStatusCode() != 404)
                return $responseDelete;
        }
        if ($home->getBackground() != null) {
            $responseDelete =  $this->forward(
                "App\Controller\MediaObjectController::deleteAction",
                ['id' => $home->getBackground()->getId()]
            );
            if ($responseDelete->getStatusCode() != 204 && $responseDelete->getStatusCode() != 404)
                return $responseDelete;
        }
        return $this->view(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    private function getHome()
    {
        $homes = $this->homeRepository->findAll();
        if (count($homes) > 1) {
            return $this->createConflictError("too.many.home.page", $this->translator);
        } else if (count($homes) === 0) {
            throw new NotFoundHttpException();
        }
        return $homes[0];
    }
}
