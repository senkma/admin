<?php

namespace App\Security;

use App\Entity\Communication;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommunicationVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Communication;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Communication $communication */
        $communication = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($communication, $user),
            self::EDIT => $this->canEdit($communication, $user),
            self::DELETE => $this->canDelete($communication, $user),
            default => false,
        };
    }

    private function canView(Communication $communication, User $user): bool
    {
        return $communication->getUser() === $user;
    }

    private function canEdit(Communication $communication, User $user): bool
    {
        return $communication->getUser() === $user;
    }

    private function canDelete(Communication $communication, User $user): bool
    {
        return $communication->getUser() === $user;
    }
}
