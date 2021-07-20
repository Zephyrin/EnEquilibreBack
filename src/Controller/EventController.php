<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
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
use Gedmo\Translatable\Entity\Translation;

/**
 * Class EventController
 * @package App\Controller
 *
 * @Route("api")
 * @SWG\Tag(
 *     name="Event"
 * )
 * 
 */
class EventController extends AbstractFOSRestController
{
    use HelperController;

    use TranslatableHelperController;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $image = "image";
    private $main = "main";
    private $showCase = "showCase";
    private $medias = "medias";
    private $id = "id";

    private $title = "title";
    private $subtitle = "subtitle";
    private $order = "order";
    private $description = "description";

    public function __construct(
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        FormErrorSerializer $formErrorSerializer,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    /**
     * Create a new Event only if user is at least commercant.
     * 
     * @Route("/{_locale}/event",
     *  name="api_event_post",
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
     *       ref=@Model(type=Event::class)
     *     )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct.<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=401,
     *     description="You are not allow to create a event."
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Event",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Event::class)
     *     ),
     *     description="The JSon Event."
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
        $this->setLang($data, $this->subtitle);
        $this->setLang($data, $this->description);
        $responseImage = $this->createOrUpdateMediaObject($data, $this->image);

        $form = $this->createForm(EventType::class, new Event());
        $form->submit($data, false);

        $validation =
            $this->validationErrorWithChild(
                $form,
                $this,
                $responseImage,
                $this->image,
                $this->translator
            );
        if ($validation instanceof JsonResponse) {
            return $validation;
        }

        $insertData = $form->getData();
        $this->translate($insertData, $this->title, $this->entityManager);
        $this->translate($insertData, $this->subtitle, $this->entityManager);
        $this->translate($insertData, $this->description, $this->entityManager);

        $this->entityManager->persist($insertData);

        $this->entityManager->flush();
        $this->setTranslation($insertData);

        return  $this->view($insertData, Response::HTTP_CREATED);
    }

    /**
     * Expose the event page information.
     *
     * @Route("/{_locale}/event/{id}",
     *  name="api_event_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d+"
     * })
     * 
     * @SWG\Get(
     *     summary="Get the Event",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return event",
     *     @SWG\Schema(ref=@Model(type=Event::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="This event does not exists"
     * )
     *
     * @return View
     */
    public function getAction(string $id)
    {
        $event = $this->getEventById($id);
        if ($event instanceof JsonResponse)
            return $event;
        return $this->view($event);
    }

    private function setTranslation(Event $event)
    {

        /** @var Gedmo\Translatable\Entity\Translation */
        $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $array = $this->createTranslatableArray();
        $this->addTranslatableVar(
            $array,
            $repository->findTranslations($event)
        );
        if ($event->getImage() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($event->getImage()),
                $this->image
            );
        $event->setTranslations($array);
        return $event;
    }

    /**
     * Expose the event page information with all languages for merchant/admin edition.
     *
     * @Route("/event/{id}",
     *  name="api_event_merchant_get",
     *  methods={"GET"},
     *  requirements={
     *      "id": "\d+"
     * })
     * 
     * @SWG\Get(
     *     summary="Get event page for admin.",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the event page.",
     *     @SWG\Schema(ref=@Model(type=Event::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="This event does not exists."
     * )
     *
     * @return View
     */
    public function getActionMerchant(string $id)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");

        $event = $this->getEventById($id);
        if ($event instanceof JsonResponse)
            return $event;
        $this->setTranslation($event);

        return $this->view($event);
    }

    /**
     * Expose all Events and their informations.
     * 
     * @Route("/{_locale}/events",
     *  name="api_event_gets",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get all Event",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all Event and their user information.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Event::class))
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
        $event = $this->eventRepository->findAllPagination($paramFetcher);
        return $this->setPaginateToView($event, $this);
    }

    /**
     * Expose all Event and their informations.
     * 
     * @Route("/events",
     *  name="api_events_gets",
     *  methods={"GET"},
     * )
     * 
     * @SWG\Get(
     *     summary="Get all Event",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all Event and their user information.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Event::class))
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
        $events = $this->eventRepository->findAllPagination($paramFetcher);
        /** @var Gedmo\Translatable\Entity\Translation */
        $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        foreach ($events[0] as $event) {
            $array = $this->createTranslatableArray();
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($event)
            );
            $event->setTranslations($array);
        }
        return $this->setPaginateToView($events, $this);
    }

    /**
     * Update a event.
     * 
     * @Route("/{_locale}/event/{id}",
     *  name="api_event_put",
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
     *     name="The full JSON event",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Event::class)
     *     ),
     *     description="The JSon Event"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Event"
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
     * Update a part of a Event page
     *
     * All missing attribute will not be update.
     *
     * @Route("/{_locale}/event/{id}",
     *  name="api_event_patch",
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
     *     description="The Event page is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON Event",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(ref=@Model(type=Event::class)),
     *     description="A part of a JSon Event"
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

    private function putOrPatch(Request $request, string $id, bool $clearData)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        $existingEvent = $this->getEventById($id);
        if ($existingEvent instanceof JsonResponse)
            return $existingEvent;
        $form = $this->createForm(EventType::class, $existingEvent);
        $data = $this->getDataFromJson($request, true, $this->translator);
        if ($data instanceof JSonResponse) {
            return $data;
        }

        $this->setLang($data, $this->title);
        $this->setLang($data, $this->subtitle);
        $this->setLang($data, $this->description);
        $responseImage = $this->createOrUpdateMediaObject($data, $this->image, $clearData);
        $form->submit($data, $clearData);

        $validation = $this->validationErrorWithChild(
            $form,
            $this,
            $responseImage,
            $this->image,
            $this->translator
        );
        if ($validation instanceof JsonResponse)
            return $validation;

        $insertData = $form->getData();
        $this->translate($insertData, $this->title, $this->entityManager, $clearData);
        $this->translate($insertData, $this->subtitle, $this->entityManager, $clearData);
        $this->translate($insertData, $this->description, $this->entityManager, $clearData);

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
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
     * Delete the event page.
     *
     * @Route("/{_locale}/event/{id}",
     *  name="api_event_delete",
     *  methods={"DELETE"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d+"
     * })
     * 
     * @SWG\Delete()
     * @SWG\Response(
     *     response=204,
     *     description="The event page is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The event page doesnot exists."
     * )
     *
     * 
     * @return View|JsonResponse
     */
    public function deleteAction(string $id)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $event = $this->getEventById($id);
        if ($event instanceof JsonResponse)
            return $event;

        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return $this->view(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    private function getEventById(string $id)
    {
        $event = $this->eventRepository->find($id);
        if (null === $event) {
            throw new NotFoundHttpException();
        }
        return $event;
    }
}
