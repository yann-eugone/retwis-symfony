<?php

namespace App\Controller;

use App\Redis\NotFoundException;
use App\User\User;
use App\User\UserStorage;

class AbstractUserController extends Controller
{
    protected UserStorage $users;

    public function __construct(UserStorage $users)
    {
        $this->users = $users;
    }

    protected function getUserByUsernameOr404(string $username): User
    {
        return $this->getUserByIdOr404($this->users->id($username));
    }

    protected function getUserByIdOr404(int $id): User
    {
        try {
            return $this->users->get($id);
        } catch (NotFoundException $exception) {
            throw $this->createNotFoundException('Not Found', $exception);
        }
    }
}
