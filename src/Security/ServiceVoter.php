<?php

namespace App\Security;

use App\Entity\Service;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ServiceVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Service;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Service $service */
        $service = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($service, $user),
            self::EDIT => $this->canEdit($service, $user),
            self::DELETE => $this->canDelete($service, $user),
            default => false,
        };
    }

    private function canView(Service $service, User $user): bool
    {
        return $service->getUser() === $user;
    }

    private function canEdit(Service $service, User $user): bool
    {
        return $service->getUser() === $user;
    }

    private function canDelete(Service $service, User $user): bool
    {
        return $service->getUser() === $user;
    }
}
