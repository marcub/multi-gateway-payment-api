<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

use App\Domain\User\Entities\User;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Role;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Exceptions\UserException;
use DateTimeImmutable;
use Illuminate\Support\Str;
use ValueError;

class UserService
{
    private UserRepositoryInterface $userRepository;
    
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticateUser(string $email, string $password): User
    {
        $email = new Email($email);
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->getHashedPassword())) {
            throw UserException::invalidCredentials();
        }

        if (!$user->getIsActive()) {
            throw UserException::inactive();
        }

        return $user;
    }

    public function registerUser(string $email, string $password, string $role): User
    {
        $email = new Email($email);

        $userFound = $this->userRepository->findByEmail($email);

        if ($userFound) {
            throw UserException::alreadyExists();
        }

        try {
            $role = Role::from(strtolower(trim($role)));
        } catch (ValueError $e) {
            throw UserException::invalidRole();
        }

        $userId = new UserId((string) Str::uuid());

        if (strlen($password) < 8) {
            throw UserException::invalidPassword();
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $dateTimeNow = new DateTimeImmutable();

        $user = new User(
            $userId,
            $email,
            $hashedPassword,
            $role,
            true,
            $dateTimeNow,
            $dateTimeNow
        );

        $this->userRepository->save($user);

        return $user;
    }

    public function updateUser(string $id, ?string $email, ?string $password, ?string $role): User
    {
        $user  = $this->userRepository->findById($id);

        if (!$user) {
            throw UserException::notFound();
        }

        $isUpdated = false;

        if ($email !== null && (string) $email !== (string) $user->getEmail()) {
            $email = new Email($email);

            $userFound = $this->userRepository->findByEmail($email);

            if ($userFound && (string) $user->getId() !== (string) $userFound->getId()) {
                throw UserException::alreadyExists();
            }

            $user->setEmail($email);
            $isUpdated = true;
        }

        if ($password !== null && !password_verify($password, $user->getHashedPassword())) {
            if (strlen($password) < 8) {
                throw UserException::invalidPassword();
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $user->setHashedPassword($hashedPassword);
            $isUpdated = true;
        }

        if ($role !== null && $role !== $user->getRole()->value) {
            try {
                $user->setRole(Role::from(strtolower(trim($role))));
                $isUpdated = true;
            } catch (ValueError $e) {
                throw UserException::invalidRole();
            }
        }

        if (!$isUpdated) {
            throw UserException::nothingToUpdate();
        }

        $user->setUpdatedAt(new DateTimeImmutable());
        $this->userRepository->save($user);

        return $user;
    }

    public function getUser(string $id): User
    {
        $user  = $this->userRepository->findById($id);

        if (!$user) {
            throw UserException::notFound();
        }

        return $user;
    }

    public function listUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function activateUser(string $id): void
    {
        $user  = $this->userRepository->findById($id);

        if (!$user) {
            throw UserException::notFound();
        }

        if ($user->getIsActive()) {
            throw UserException::alreadyActive();
        }

        $user->setIsActive(true);
        $user->setUpdatedAt(new DateTimeImmutable());

        $this->userRepository->save($user);
    }

    public function deactivateUser(string $id): void
    {
        $user  = $this->userRepository->findById($id);

        if (!$user) {
            throw UserException::notFound();
        }

        if (!$user->getIsActive()) {
            throw UserException::alreadyInactive();
        }

        $user->setIsActive(false);
        $user->setUpdatedAt(new DateTimeImmutable());
        
        $this->userRepository->save($user);
    }
}
