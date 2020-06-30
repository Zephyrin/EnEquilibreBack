<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $body = [
            'status' => JsonResponse::HTTP_FORBIDDEN,
            'message' => $this->translator->trans('access.denied', [], "messages")
        ];
        return new JsonResponse($body, JsonResponse::HTTP_FORBIDDEN);
    }
}
