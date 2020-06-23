<?php

namespace App\Controller;

use App\Entity\MediaObject;
use App\Repository\MediaObjectRepository;
use App\Form\MediaObjectType;
use App\Controller\Helpers\HelperController;
use App\Controller\Helpers\MediaObjectHelperController;
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
 * Class MediaObjectController
 * @package App\Controller
 * @Route("/api")
 * @SWG\Tag(
 *     name="MediaObject"
 * )
 */
class MediaObjectController extends AbstractFOSRestController 
{
    use TranslatableHelperController;

    use HelperController;
    /**
     * Helper to save the image into a folder.
     */
    use MediaObjectHelperController;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var mediaObjectRepository
     */
    private $mediaObjectRepository;
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
    private string $description = "description"; 

    public function __construct(
        EntityManagerInterface $entityManager,
        MediaObjectRepository $mediaObjectRepository,
        FormErrorSerializer $formErrorSerializer,
        TranslatorInterface $translator

    ) {
        $this->entityManager = $entityManager;
        $this->mediaObjectRepository = $mediaObjectRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    /**
     * Upload an image using MediaObject
     * @Route("/{_locale}/mediaobject",
     *  name="api_mediaobject_post",
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
     *       ref=@Model(type=MediaObject::class)
     *      )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allow to create a link for an another user."
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON MediaObject",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=MediaObject::class)
     *     ),
     *     description="The JSon MediaObject"
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
        if($data instanceof JsonResponse) 
            return $data;
        $this->setLang($data, $this->description);
        $form = $this->createForm(
            MediaObjectType::class,
            new MediaObject()
        );
        $form = $form->submit($data, false);
        $validation = $this->validationError($form, $this, $this->translator);
        if($validation instanceof JsonResponse)
            return $validation;

        $mediaObject = $form->getData();
        $this->translate($mediaObject, $this->description, $this->entityManager);
        try
        {
            $mediaObject->setFilePath($this->manageImage($data, $this->translator));
        } catch(Exception $e) {
            return $this->formatErrorManageImage($data, $e, $this->translator);
        }
        $this->entityManager->persist($mediaObject);
        $this->entityManager->flush();
        return  $this->view(
            $mediaObject,
            Response::HTTP_CREATED
        );
    }

    /**
     * Expose the MediaObject.
     * 
     * @Route("/{_locale}/mediaobject/{id}",
     *  name="api_mediaobject_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d"
     * })
     *
     * @SWG\Get(
     *     summary="Get the MediaObject based on its ID.",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the MediaObject Entity based on ID.",
     *     @SWG\Schema(ref=@Model(type=MediaObject::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The MediaObject based on ID does not exists."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the information about MediaObject."
     * )
     *
     *
     * @param string $id
     * @return View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findMediaObjectById($id)
        );
    }

    /**
     * Expose all MediaObjects and their informations.
     * 
     * @Route("/{_locale}/mediaobjects",
     *  name="api_mediaobject_gets",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get all MediaObjects",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all MediaObjects and their user information.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=MediaObject::class))
     *     )
     * )
     *
     * @param Request $request
     * @return View
     */
    public function cgetAction(Request $request)
    {
        return $this->view(
            $this->mediaObjectRepository->findAll()
        );
    }

    /**
     * Update an MediaObject entirely.
     * Warning: Languages are not taken into account yet. If one language is missing, 
     * then this language will not change and will not be delete. For a language it's more like a PATCH.
     *
     * @Route("/{_locale}/mediaobject/{id}",
     *  name="api_mediaobject_put",
     *  methods={"PUT"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d"
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
     *     response=404,
     *     description="The MediaObject based on ID is not found."
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON MediaObject.",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=MediaObject::class)
     *     ),
     *     description="The JSon MediaObject."
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the MediaObject."
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the MediaObject to update.
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function putAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, true);
    }

    /**
     * Update a part of a MediaObject.
     * 
     * @Route("/{_locale}/mediaobject/{id}",
     *  name="api_mediaobject_patch",
     *  methods={"PATCH"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d"
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
     *     description="The MediaObject based on ID is not found."
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allowed to update a MediaObject."
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON MediaObject",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=MediaObject::class)
     *     ),
     *     description="The JSon MediaObject."
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the MediaObject."
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the MediaObject to update
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, false);
    }

    /**
     * Delete an MediaObject with the id.
     *
     * @Route("/{_locale}/mediaobject/{id}",
     *  name="api_mediaobject_delete",
     *  methods={"DELETE"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d"
     * })
     * 
     * @SWG\Delete(
     *     summary="Delete an MediaObject based on ID."
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The MediaObject is correctly delete.",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The MediaObject based on ID is not found."
     * )

     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the MediaObject."
     * )
     * @param string $id
     * @param Request $request
     * @return View
     */
    public function deleteAction(Request $request, string $id)
    {
        $mediaObject = $this->findMediaObjectById($id);
        unlink($this->getParameter('media_object') . "/" . $mediaObject->getFilePath());
        $this->entityManager->remove($mediaObject);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return MediaObject
     * @throws NotFoundHttpException
     */
    private function findMediaObjectById(string $id)
    {
        $existingMediaObject = $this->mediaObjectRepository->find($id);
        if (null === $existingMediaObject) {
            throw new NotFoundHttpException();
        }
        return $existingMediaObject;
    }

    /**
     * @param Request $request
     * @param string $id
     * @param bool $clearMissing
     * @return View|JsonResponse
     * @throws ExceptionInterface
     * @throws Exception
     */
    private function putOrPatch(Request $request, string $id, bool $clearMissing)
    {
        $existingMediaObjectField = $this->findMediaObjectById($id);
        $form = $this->createForm(MediaObjectType::class, $existingMediaObjectField);
        $data = $this->getDataFromJson($request, true, $this->translator);
        if($data instanceof JsonResponse) 
            return $data;
        $this->setLang($data, $this->description);
        $form->submit($data, $clearMissing);
        $validation = $this->validationError($form, $this, $this->translator);
        if($validation instanceof JsonResponse)
            return $validation;
        $mediaObject = $form->getData();
        $this->translate($mediaObject, $this->description, $this->entityManager, $clearMissing);
        $mediaObject->setFilePath(
            $this->manageImage($data,
                               $this->translator,
                               $mediaObject->getFilePath()));

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }


}
