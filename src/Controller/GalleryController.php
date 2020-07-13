<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Form\GalleryType;
use App\Repository\GalleryRepository;
use App\Controller\Helpers\HelperController;
use App\Controller\Helpers\TranslatableHelperController;
use App\Entity\MediaObject;
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
 * Class GalleryController
 * @package App\Controller
 *
 * @Route("api")
 * @SWG\Tag(
 *     name="Gallery"
 * )
 * 
 */
class GalleryController extends AbstractFOSRestController
{
    use HelperController;

    use TranslatableHelperController;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var GalleryRepository
     */
    private $galleryRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $separator = "separator";
    private $main = "main";
    private $showCase = "showCase";
    private $medias = "medias";
    private $id = "id";

    private $title = "title";
    private $order = "order";

    public function __construct(
        EntityManagerInterface $entityManager,
        GalleryRepository $galleryRepository,
        FormErrorSerializer $formErrorSerializer,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->galleryRepository = $galleryRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    /**
     * Create a new Gallery page only if user is at least commercant.
     * 
     * @Route("/{_locale}/gallery",
     *  name="api_gallery_post",
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
     *       ref=@Model(type=Gallery::class)
     *     )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct.<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=401,
     *     description="You are not allow to create a gallery."
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Gallery",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Gallery::class)
     *     ),
     *     description="The JSon Gallery."
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

        $data = $this->getDataFromJson($request, true, $this->translator);

        if ($data instanceof JsonResponse)
            return $data;
        $this->setLang($data, $this->title);
        $responseSeparator = $this->createOrUpdateMediaObject($data, $this->separator);
        $responseMain = $this->createOrUpdateMediaObject($data, $this->main);
        $responseShowCase = $this->createOrUpdateMediaObject($data, $this->showCase);

        $form = $this->createForm(GalleryType::class, new Gallery());
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
            $responseMain,
            $this->main,
            $this->translator
        );
        if ($validation instanceof JsonResponse) {
            return $validation;
        }
        $validation = $this->validationErrorWithChild(
            $form,
            $this,
            $responseShowCase,
            $this->showCase,
            $this->translator
        );
        if ($validation instanceof JsonResponse) {
            return $validation;
        }

        $insertData = $form->getData();
        $this->translate($insertData, $this->title, $this->entityManager);

        $this->entityManager->persist($insertData);

        $this->entityManager->flush();
        return  $this->view($insertData, Response::HTTP_CREATED);
    }

    /**
     * Expose the gallery page information.
     *
     * @Route("/{_locale}/gallery/{id}",
     *  name="api_gallery_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d+"
     * })
     * 
     * @SWG\Get(
     *     summary="Get the Gallery",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return gallery",
     *     @SWG\Schema(ref=@Model(type=Gallery::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="This gallery does not exists"
     * )
     *
     * @return View
     */
    public function getAction(string $id)
    {
        $gallery = $this->getGalleryById($id);
        if ($gallery instanceof JsonResponse)
            return $gallery;
        return $this->view($gallery);
    }

    /**
     * Expose the gallery page information with all languages for merchant/admin edition.
     *
     * @Route("/gallery/{id}",
     *  name="api_gallery_merchant_get",
     *  methods={"GET"},
     *  requirements={
     *      "id": "\d+"
     * })
     * 
     * @SWG\Get(
     *     summary="Get gallery page for admin.",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the gallery page.",
     *     @SWG\Schema(ref=@Model(type=Gallery::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="This gallery does not exists."
     * )
     *
     * @return View
     */
    public function getActionMerchant(string $id)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");

        $gallery = $this->getGalleryById($id);
        if ($gallery instanceof JsonResponse)
            return $gallery;
        $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $array = $this->createTranslatableArray();
        $this->addTranslatableVar(
            $array,
            $repository->findTranslations($gallery)
        );
        if ($gallery->getMain() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($gallery->getMain()),
                $this->main
            );
        if ($gallery->getSeparator() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($gallery->getSeparator()),
                $this->separator
            );
        if ($gallery->getShowCase() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($gallery->getShowCase()),
                $this->separator
            );
        $gallery->setTranslations($array);

        return $this->view($gallery);
    }

    /**
     * Expose all Gallerys and their informations.
     * 
     * @Route("/{_locale}/galleries",
     *  name="api_gallery_gets",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get all Gallery",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all Gallery and their user information.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Gallery::class))
     *     )
     * )
     * 
     * @QueryParam(name="page"
     * , requirements="\d+"
     * , default="1"
     * , description="Page of the overview.")
     * @QueryParam(name="limit"
     * , requirements="\d+"
     * , default="10"
     * , description="Item count limit")
     * @QueryParam(name="sort"
     * , requirements="(asc|desc)"
     * , allowBlank=false
     * , default="asc"
     * , description="Sort direction")
     * @QueryParam(name="sortBy"
     * , requirements="(id|order)"
     * , default="order"
     * , description="Sort by name or uri")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="for instance i don't know")
     *
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $gallery = $this->galleryRepository->findAllPagination($paramFetcher);
        return $this->setPaginateToView($gallery, $this);
    }

    /**
     * Expose all Gallery and their informations.
     * 
     * @Route("/galleries",
     *  name="api_galleries_gets",
     *  methods={"GET"},
     * )
     * 
     * @SWG\Get(
     *     summary="Get all Gallery",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all Gallery and their user information.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Gallery::class))
     *     )
     * )
     * 
     * @QueryParam(name="page"
     * , requirements="\d+"
     * , default="1"
     * , description="Page of the overview.")
     * @QueryParam(name="limit"
     * , requirements="\d+"
     * , default="10"
     * , description="Item count limit")
     * @QueryParam(name="sort"
     * , requirements="(asc|desc)"
     * , allowBlank=false
     * , default="asc"
     * , description="Sort direction")
     * @QueryParam(name="sortBy"
     * , requirements="(id|order)"
     * , default="order"
     * , description="Sort by name or uri")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="for instance i don't konw.")
     *
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function cgetActionMerchant(ParamFetcher $paramFetcher)
    {
        $galleries = $this->galleryRepository->findAllPagination($paramFetcher);
        $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        foreach ($galleries[0] as $gallery) {
            $array = $this->createTranslatableArray();
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($gallery)
            );
            $gallery->setTranslations($array);
        }
        return $this->setPaginateToView($galleries, $this);
    }

    /**
     * Update a gallery.
     * 
     * @Route("/{_locale}/gallery/{id}",
     *  name="api_gallery_put",
     *  methods={"PUT"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d+"
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
     *     name="The full JSON gallery",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Gallery::class)
     *     ),
     *     description="The JSon Gallery"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Gallery"
     *    )
     * )
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function putAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, true);
    }

    /**
     * Update a part of a Gallery page
     *
     * All missing attribute will not be update.
     *
     * @Route("/{_locale}/gallery/{id}",
     *  name="api_gallery_patch",
     *  methods={"PATCH"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d+"
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
     *     description="The Gallery page is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON Gallery",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(ref=@Model(type=Gallery::class)),
     *     description="A part of a JSon Gallery"
     *    )
     * )
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function patchAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, false);
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

    private function putOrPatch(Request $request, string $id, bool $clearData)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        $existingGallery = $this->getGalleryById($id);
        if ($existingGallery instanceof JsonResponse)
            return $existingGallery;
        $form = $this->createForm(GalleryType::class, $existingGallery);
        $data = $this->getDataFromJson($request, true, $this->translator);
        if ($data instanceof JSonResponse) {
            return $data;
        }

        $this->setLang($data, $this->title);
        $responseSeparator = $this->createOrUpdateMediaObject($data, $this->separator, $clearData);
        $responseMain = $this->createOrUpdateMediaObject($data, $this->main, $clearData);
        $responseShowCase = $this->createOrUpdateMediaObject($data, $this->showCase, $clearData);
        $this->manageArrayMediaObject($data, $this->medias, $existingGallery);
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
            $responseMain,
            $this->main,
            $this->translator
        );
        if ($validation instanceof JsonResponse) {
            return $validation;
        }
        $validation = $this->validationErrorWithChild(
            $form,
            $this,
            $responseShowCase,
            $this->showCase,
            $this->translator
        );
        if ($validation instanceof JsonResponse) {
            return $validation;
        }
        $insertData = $form->getData();
        $this->translate($insertData, $this->title, $this->entityManager, $clearData);

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    private function manageArrayMediaObject(array &$data, string $field, Gallery $gallery)
    {
        if (isset($data[$field])) {
            foreach ($data[$field] as $media) {
                $find = false;
                if (!isset($media['id'])) {
                    return;
                }
                foreach ($gallery->getMedias() as $in_media) {
                    if ($in_media->getId() === $media['id']) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $gallery->addMedia($this->findMediaById($media['id']));
                }
            }
            foreach ($gallery->getMedias() as $media) {
                $find = false;
                foreach ($data[$field] as $out_media) {
                    if ($out_media['id'] === $media->getId()) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $gallery->removeMedia($media);
                }
            }
            unset($data[$field]);
        }
    }

    /**
     * @param string $id
     *
     * @return MediaObject
     * @throws NotFoundHttpException
     */
    private function findMediaById(string $id)
    {
        $media = $this->entityManager->find(
            MediaObject::class,
            $id
        );
        if ($media == null)
            throw new NotFoundHttpException();
        return $media;
    }
    /**
     * Delete the gallery page.
     *
     * @Route("/{_locale}/gallery/{id}",
     *  name="api_gallery_delete",
     *  methods={"DELETE"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d+"
     * })
     * 
     * @SWG\Delete()
     * @SWG\Response(
     *     response=204,
     *     description="The gallery page is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The gallery page doesnot exists."
     * )
     *
     * 
     * @return View|JsonResponse
     */
    public function deleteAction(string $id)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $gallery = $this->getGalleryById($id);
        if ($gallery instanceof JsonResponse)
            return $gallery;

        $this->entityManager->remove($gallery);
        $this->entityManager->flush();

        return $this->view(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    private function getGalleryById(string $id)
    {
        $gallery = $this->galleryRepository->find($id);
        if (null === $gallery) {
            throw new NotFoundHttpException();
        }
        return $gallery;
    }
}
