<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
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
 * Class ContactController
 * @package App\Controller
 *
 * @Route("api")
 * @SWG\Tag(
 *     name="Contact"
 * )
 * 
 */
class ContactController extends AbstractFOSRestController
{
    use HelperController;

    use TranslatableHelperController;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ContactRepository
     */
    private $contactRepository;

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

    private $contact = "contact";
    private $comment = "comment";
    private $email = "email";

    public function __construct(
        EntityManagerInterface $entityManager,
        ContactRepository $contactRepository,
        FormErrorSerializer $formErrorSerializer,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->contactRepository = $contactRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    /**
     * Create a new Contact page only if user is at least commercant.
     * 
     * @Route("/{_locale}/contact",
     *  name="api_contact_post",
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
     *       ref=@Model(type=Contact::class)
     *     )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct.<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=401,
     *     description="You are not allow to create a contact page for an another user."
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Contact",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Contact::class)
     *     ),
     *     description="The JSon Contact page. Only used to create the contact page at initialisation."
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
        /* Check if there is another contact page. */
        try {
            $contact = $this->getContact();
            if ($contact instanceof JsonResponse)
                return $contact;
            else
                return $this->createConflictError("contact.already.exists", $this->translator);
        } catch (NotFoundHttpException $e) {
            // Ok, this is what we need to create a new contact page.
        }
        $data = $this->getDataFromJson($request, true, $this->translator);

        if ($data instanceof JsonResponse)
            return $data;
        $this->setLang($data, $this->contact);
        $this->setLang($data, $this->comment);
        $responseSeparator = $this->createOrUpdateMediaObject($data, $this->separator);
        $responseBackground = $this->createOrUpdateMediaObject($data, $this->background);

        $form = $this->createForm(ContactType::class, new Contact());
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
        $this->translate($insertData, $this->contact, $this->entityManager);
        $this->translate($insertData, $this->comment, $this->entityManager);

        $this->entityManager->persist($insertData);

        $this->entityManager->flush();
        return  $this->view($insertData, Response::HTTP_CREATED);
    }

    /**
     * Expose the Contact page information.
     *
     * @Route("/{_locale}/contact",
     *  name="api_contact_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get the Contact page",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Contact page",
     *     @SWG\Schema(ref=@Model(type=Contact::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Contact page does not exists yet"
     * )
     *
     * @return View
     */
    public function getAction()
    {
        $contact = $this->getContact();
        if ($contact instanceof JsonResponse)
            return $contact;
        return $this->view($contact);
    }

    /**
     * Expose the Contact page information with all languages for merchant/admin edition.
     *
     * @Route("/contact",
     *  name="api_contact_merchant_get",
     *  methods={"GET"})
     * 
     * @SWG\Get(
     *     summary="Get the Contact page",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Contact page",
     *     @SWG\Schema(ref=@Model(type=Contact::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Contact page does not exists yet"
     * )
     *
     * @return View
     */
    public function getActionMerchant()
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");

        $contact = $this->getContact();
        if ($contact instanceof JsonResponse)
            return $contact;
        /** @var Gedmo\Translatable\Entity\Translation */
        $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $array = $this->createTranslatableArray();
        $this->addTranslatableVar(
            $array,
            $repository->findTranslations($contact)
        );
        if ($contact->getBackground() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($contact->getBackground()),
                $this->background
            );
        if ($contact->getSeparator() != null)
            $this->addTranslatableVar(
                $array,
                $repository->findTranslations($contact->getSeparator()),
                $this->separator
            );
        $contact->setTranslations($array);

        return $this->view($contact);
    }

    /**
     * Update a Contact page.
     * 
     * @Route("/{_locale}/contact",
     *  name="api_contact_put",
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
     *     name="The full JSON Contact",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Contact::class)
     *     ),
     *     description="The JSon Contact"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Contact"
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
     * Update a part of a Contact page
     *
     * All missing attribute will not be update.
     *
     * @Route("/{_locale}/contact",
     *  name="api_contact_patch",
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
     *     description="The Contact page is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON Contact",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(ref=@Model(type=Contact::class)),
     *     description="A part of a JSon Contact"
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
        $existingContact = $this->getContact();
        if ($existingContact instanceof JsonResponse)
            return $existingContact;
        $form = $this->createForm(ContactType::class, $existingContact);
        if (count($this->contactRepository->findAll()) > 1) {
            return $this->createError($form, $this, $this->translator, "contact.already.exists");
        }
        $data = $this->getDataFromJson($request, true, $this->translator);
        if ($data instanceof JSonResponse) {
            return $data;
        }

        $this->setLang($data, $this->contact);
        $this->setLang($data, $this->comment);
        /* $separator = $existingContact->getSeparator();
        $background = $existingContact->getBackground(); */
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
        $this->translate($insertData, $this->contact, $this->entityManager, $clearData);
        $this->translate($insertData, $this->comment, $this->entityManager, $clearData);

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete the contact page.
     * 
     * You should know what your are doing ! Cannot be reverse.
     *
     * @Route("/{_locale}/contact",
     *  name="api_contact_delete",
     *  methods={"DELETE"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Delete()
     * @SWG\Response(
     *     response=204,
     *     description="The contact page is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The contact page doesnot exists."
     * )
     *
     * 
     * @return View|JsonResponse
     */
    public function deleteAction()
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $contact = $this->getContact();
        if ($contact instanceof JsonResponse)
            return $contact;

        $this->entityManager->remove($contact);
        $this->entityManager->flush();
        return $this->view(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    private function getContact()
    {
        $contacts = $this->contactRepository->findAll();
        if (count($contacts) > 1) {
            return $this->createConflictError("contact.already.exists", $this->translator);
        } else if (count($contacts) === 0) {
            throw new NotFoundHttpException();
        }
        return $contacts[0];
    }
}
