<?php

namespace App\Controller;

use App\Entity\JSonLD;
use App\Repository\JSonLDRepository;
use App\Form\JSonLDType;
use App\Controller\Helpers\HelperController;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use Behat\Behat\HelperContainer\Exception\NotFoundException;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use JsonException;
use phpDocumentor\Reflection\Types\Integer;


/**
 * Class JSonLDController
 * which just save JSon description of each page if need.
 * @package App\Controller
 * 
 * @Route("/api")
 * @SWG\Tag(
 *  name="JSonLD"
 * )
 */
class JSonLDController extends AbstractFOSRestController
{
    use HelperController;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var JSonLDRepository
     */
    private $repository;
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        JSonLDRepository $repository,
        FormErrorSerializer $formErrorSerializer,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->translator = $translator;
    }

    /**
     * Upload a JSonLD as a text directly
     * @Route("/{_language}/{_page}/jsonld",
     *  name="api_jsonld_post",
     *  methods={"POST"},
     *  requirements={
     *      "_language": "en|fr"
     * })
     * 
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="The new JSon LD is insert into database.",
     *      @SWG\Schema(
     *       ref=@Model(type=string::class)
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
     *     name="The JSON LD directly. With everything.",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=string::class)
     *     ),
     *     description="The JSon LD string to save."
     *    )
     *
     * )
     *
     * @param Request $request
     * @return View|JsonResponse
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function postAction(Request $request, string $_page)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        $data = $this->getDataFromJson($request, true, $this->translator);

        if ($data instanceof JsonResponse)
            return $data;
        try {
            $json = $this->getJSonLD($_page);
            return $this->createConflictError("json.ld.already.exists", $this->translator);
        } catch (NotFoundHttpException $e) {
            // This is what we need to create a new JSonLD
        }
        return $this->post($_page, $data);
    }

    public function post($page, $data)
    {
        $form = $this->createForm(
            JSonLDType::class,
            new JSonLD()
        );

        $form = $form->submit($data, false);
        $validation = $this->validationError($form, $this, $this->translator);
        if ($validation instanceof JsonResponse)
            return $validation;

        $object = $form->getData();
        $object->setId($page);

        $this->entityManager->persist($object);
        $this->entityManager->flush();
        return  $this->view(
            $object,
            Response::HTTP_CREATED
        );
    }

    /**
     * Expose a JSonLD.
     * 
     * @Route("/{id}/jsonld",
     *  name="api_jsonld_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     *
     * @SWG\Get(
     *     summary="Get the JSonLD based on its ID.",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the JSonLD Entity based on ID.",
     *     @SWG\Schema(ref=@Model(type=JSonLD::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The JSonLD based on ID does not exists."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the information about JSonLD."
     * )
     *
     *
     * @param string $id
     * @return View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->getJSonLD($id)
        );
    }

    /**
     * Expose all JSonLDs and their informations.
     * 
     * @Route("/{_locale}/jsonlds",
     *  name="api_jsonld_gets",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get all JSonLDs",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all JSonLDs and their user information.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=JSonLD::class))
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
     * , requirements="(id|description|filePath)"
     * , default="id"
     * , description="Sort by name or uri")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="Search on name or description or sub-category name or category name or brand name or brand description")
     *
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $jsonlds = $this->jsonldRepository->findAllPagination($paramFetcher);
        return $this->setPaginateToView($jsonlds, $this);
    }

    /**
     * Update an JSonLD entirely.
     * Warning: Languages are not taken into account yet. If one language is missing, 
     * then this language will not change and will not be delete. For a language it's more like a PATCH.
     *
     * @Route("/{_locale}/{_page}/jsonld",
     *  name="api_jsonld_put",
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
     *      description="Successful operation."
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct."
     *    ),
     *    @SWG\Response(
     *     response=404,
     *     description="The JSonLD based on ID is not found."
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON JSonLD.",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=JSonLD::class)
     *     ),
     *     description="The JSon JSonLD."
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the JSonLD."
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the JSonLD to update.
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function putAction(Request $request, string $_page)
    {
        return $this->putOrPatch($this->getDataFromJson($request, true, $this->translator), $_page, true);
    }

    /**
     * Update a part of a JSonLD.
     * 
     * @Route("/{_locale}/{_page}/jsonld",
     *  name="api_jsonld_patch",
     *  methods={"PATCH"},
     *  requirements={
     *      "_locale": "en|fr"
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
     *     description="The JSonLD based on ID is not found."
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allowed to update a JSonLD."
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON JSonLD",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=JSonLD::class)
     *     ),
     *     description="The JSon JSonLD."
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the JSonLD."
     *    )
     * )
     *
     * @param Request $request
     * @param string $_page of the JSonLD to update
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function patchAction(Request $request, string $_page)
    {
        return $this->putOrPatch($this->getDataFromJson($request, true, $this->translator), $_page, false);
    }

    /**
     * Delete an JSonLD with the id.
     *
     * @Route("/{_locale}/{_page}/jsonld",
     *  name="api_jsonld_delete",
     *  methods={"DELETE"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Delete(
     *     summary="Delete an JSonLD based on ID."
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The JSonLD is correctly delete.",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The JSonLD based on ID is not found."
     * )
     * 
     * @SWG\Response(
     *      response=403,
     *      description="You are not authorizel to delete this JSonLD"
     * )

     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the JSonLD."
     * )
     * @param string $_page
     * @throws Exception
     * @return View
     */
    public function deleteAction(string $_page)
    {
        $this->denyAccessUnlessGranted("ROLE_MERCHANT");
        $jsonld = $this->getJSonLD($_page);

        $this->entityManager->remove($jsonld);
        $this->entityManager->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return JSonLD
     * @throws NotFoundHttpException
     */
    private function getJSonLD(string $id)
    {
        $existing = $this->repository->find($id);
        if (null === $existing) {
            throw new NotFoundHttpException();
        }
        return $existing;
    }

    /**
     * @param array $request
     * @param string $_page
     * @param bool $clearMissing
     * @return View|JsonResponse
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function putOrPatch(array $data, string $_page, bool $clearMissing)
    {
        $existingJSonLDField = $this->getJSonLD($_page);
        $form = $this->createForm(JSonLDType::class, $existingJSonLDField);
        if ($data instanceof JsonResponse)
            return $data;
        $form->submit($data, $clearMissing);
        $validation = $this->validationError($form, $this, $this->translator);
        if ($validation instanceof JsonResponse)
            return $validation;
        $jsonld = $form->getData();

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
