<?php

namespace App\EventListener;

use App\Entity\User as LUser;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }
        if ($user instanceof LUser) {
            $user->setLastLogin(new DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else if ($user instanceof User) {
            $existingUser = new LUser();
            $existingUser->setUsername($user->getUsername());
            $existingUser->setPassword($user->getPassword());
            $existingUser->setRoles(['ROLE_SUPER_ADMIN']);
            $existingUser->setEmail($user->getUsername());
            $existingUser->setLastLogin(new DateTime('1953-05-23'));
            $this->entityManager->persist($existingUser);
            $this->entityManager->flush();
        }
    }
}
