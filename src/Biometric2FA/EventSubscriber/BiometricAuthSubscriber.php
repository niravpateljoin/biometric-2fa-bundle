<?php

declare(strict_types=1);

namespace Biometric2FA\EventSubscriber;

use Biometric2FA\Controller\BiometricController;
use Biometric2FA\Controller\JavascriptController;
use Biometric2FA\Security\BiometricUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;

readonly class BiometricAuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RedirectController $redirectController,
        private string $redirectPath,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => ['onLoginSuccess', 4],
            KernelEvents::CONTROLLER => ['onControllerEvent', 4],
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        $request = $event->getRequest();

        if (!$user instanceof BiometricUserInterface) {
            return;
        }

        if ($user->isBiometric2FAEnabled()) {
            $request->getSession()->set('biometric_verification', false);
        }
    }

    public function onControllerEvent(ControllerEvent $event): void
    {
        $currentUser = $this->tokenStorage->getToken()?->getUser();
        $request = $event->getRequest();

        if (!$currentUser instanceof BiometricUserInterface) {
            return;
        }

        $controller = $event->getController();
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        $session = $request->getSession();
        $biometricVerification = $session->get('biometric_verification');

        $isOnBiometricRoute =
            $controller instanceof BiometricController ||
            $controller instanceof JavascriptController ||
            $controller instanceof ErrorController;

        if ($currentUser->isBiometric2FAEnabled()) {
            if (!$biometricVerification && !$isOnBiometricRoute) {
                $response = $this->redirectController->redirectAction($request, 'app_biometric_auth');
                $event->setController(fn() => $response);
            }

            if ($biometricVerification && $controller instanceof BiometricController) {
                $response = $this->redirectController->redirectAction($request, $this->redirectPath);;
                $event->setController(fn() => $response);
            }
        }
    }

}
