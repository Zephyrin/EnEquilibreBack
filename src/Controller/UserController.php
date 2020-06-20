<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\Type\UserType;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use App\Serializer\FormErrorSerializer;
/**
 * @Route("/api")
 * @SWG\Tag(
 *     name="User"
 * )
 */
class UserController extends AbstractController
{
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        FormErrorSerializer $formErrorSerializer,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Register an user to the DB.
     *
     * @Route("/auth/register", name="api_auth_register",  methods={"POST"})
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
            UserType::class
            , new User()
        );

        $form->submit($data, false);

        if(false == $form->isValid())
        {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $user = $form->getData();
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $user->getPassword()
        ));

        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        # Code 307 preserves the request method, while redirectToRoute() is a shortcut method.
        return $this->redirectToRoute('api_login_check', [
            'username' => $data['username'],
            'password' => $data['password']
        ], 307);
    }
}