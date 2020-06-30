<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\Type\UserType;
use App\Controller\Helpers\HelperController;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use App\Serializer\FormErrorSerializer;
use Symfony\Contracts\Translation\TranslatorInterface;
use DateTime;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * @Route("/api")
 * @SWG\Tag(
 *     name="User"
 * )
 */
class UserController extends AbstractFOSRestController
{
    use HelperController;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        FormErrorSerializer $formErrorSerializer,
        UserPasswordEncoderInterface $passwordEncoder,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->passwordEncoder = $passwordEncoder;
        $this->translator = $translator;
    }

    /**
     * Register an user to the DB.
     *
     * @Route("/{_locale}/auth/register", name="api_auth_register",  methods={"POST"}, requirements={"_locale": "en|fr"})
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=307,
     *      description="Redirect to login form with the user as parameter"
     *    ),
     *    @SWG\Response(
     *     response=500,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Characteristic",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=User::class)
     *     ),
     *     description="The JSon Characteristic"
     *    )
     * )
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws ExceptionInterface
     * @throws ExceptionInterface
     */
    public function register(Request $request)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(
            UserType::class,
            new User()
        );
        $form->submit($data, false);
        $validation = $this->validationError($form, $this, $this->translator);
        if ($validation instanceof JsonResponse)
            return $validation;

        $user = $form->getData();
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $user->getPassword()
        ));

        $user->setRoles(['ROLE_USER']);
        $user->setCreated(new DateTime());
        $user->setLastLogin(new DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        # Code 307 preserves the request method, while redirectToRoute() is a shortcut method.
        return $this->redirectToRoute('api_login_check', [
            'username' => $data['username'],
            'password' => $data['password']
        ], 307);
    }

    /**
     * Update a part of an User
     * All missing attribute will not be update.
     * 
     * @Route("/{_locale}/user/{id}",
     *  name="api_user_patch",
     *  methods={"PATCH"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d"
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
     *     description="The User based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON User",
     *     in="body",
     *     required=true,
     *     @SWG\Property(
     *      type="string"
     *     ),
     *     description="A part of a JSon User"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the User"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the User to update
     * @return View|JsonResponse
     * @throws ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        $existingUser = $this->findUserById($id);
        $connectUser = $this->getUser();
        if ($existingUser != $connectUser)
            $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $password = $existingUser->getPassword();
        $existingUser->setPassword("tmp_tmp#tmp_tmp");
        $form = $this->createForm(UserType::class, $existingUser);

        $partialUser = $this->getDataFromJson($request, true, $this->translator);
        if ($partialUser instanceof JsonResponse)
            return $partialUser;
        if (isset($partialUser["roles"])) {
            if ($partialUser["roles"] != $existingUser->getRoles())
                $this->denyAccessUnlessGranted("ROLE_ADMIN");
        }

        $form->submit($partialUser, false);
        $validation = $this->validationError($form, $this, $this->translator);
        if ($validation instanceof JsonResponse)
            return $validation;

        $user = $form->getData();
        if (array_key_exists("password", $partialUser)) {
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
        } else {
            $user->setPassword($password);
        }
        if (array_key_exists("roles", $partialUser)) {
            foreach ($partialUser["roles"] as $role) {
                $user->setRoles($partialUser["roles"]);
            }
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Expose user.
     *  
     * @Route("/{_locale}/user/{id}",
     *  name="api_user_get",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr",
     *      "id": "\d"
     * })
     *
     * @SWG\Get(
     *     summary="Get the user based on its ID, username or email.",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the User Entity based on ID, username or email.",
     *     @SWG\Schema(ref=@Model(type=User::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The User based on ID, username or email does not exists."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID or Username Or Email used to find the information about User."
     * )
     * @param string username
     * 
     * @return User
     * @throws NotFoundHttpException
     */
    public function getAction(string $username)
    {
        return $this->view($this->findUserById($username));
    }

    /**
     * Expose all Users
     * 
     * @Route("/{_locale}/users",
     *  name="api_users_gets",
     *  methods={"GET"},
     *  requirements={
     *      "_locale": "en|fr"
     * })
     * 
     * @SWG\Get(
     *     summary="Get all Users",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Users",
     *     @SWG\Schema(
     *      type="string"
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
     * , requirements="(username|roles|email|lastLogin)"
     * , default="username"
     * , description="Sort by name or uri")
     * @QueryParam(name="search"
     * , nullable=true
     * , description="Search on name and uri")
     *
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $sort = $paramFetcher->get('sort');
        $sortBy = $paramFetcher->get('sortBy');
        $search = $paramFetcher->get('search');
        $usersAndCount = $this->userRepository->findByParams(
            $page,
            $limit,
            $sort,
            $sortBy,
            $search
        );
        return $this->setPaginateToView($usersAndCount, $this);
    }

    /**
     * @param string $id can be id, username or email.
     *
     * @return User
     * @throws NotFoundHttpException
     */
    private function findUserById(string $id)
    {
        $existingUser = $this->userRepository->findUserByUsernameOrEmail($id);
        if (null === $existingUser)
            throw new NotFoundHttpException();
        return $existingUser;
    }
}
