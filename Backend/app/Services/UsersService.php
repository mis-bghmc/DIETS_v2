<?php

namespace App\Services;

use App\Services\Interfaces\UsersServiceInterface;
use App\Repositories\Interfaces\UsersRepositoryInterface;
use App\Models\User;

class UsersService implements UsersServiceInterface
{
    protected $users_repository;

    //  Constructor
    public function __construct(UsersRepositoryInterface $users_repository)
    {
        $this->users_repository = $users_repository;
    }

    //  Authenticate
    public function authenticate($empid, $enccode, $hpercode)
    {
        try {
            // 1. Get user details from repository
            $user = $this->users_repository->getUserDetails($empid);

            // Check if user record exists
            if (empty((array) $user)) {
                return view('unauthenticated');
            }

            // 2. Get username using employee ID
            $username = $this->users_repository->searchEmployeeUsername($empid);

            if (empty($username)) {
                return view('error');
            }

            // 3. Check if the user exists in the users table
            $userModel = User::where('username', $username)->first();

            if (!$userModel) {
                return redirect(env('APP_FRONTEND_TUNNEL'));
            }

            // 4. Create token and prepare details for redirect
            $details = array_merge(
                [
                    'enccode' => $enccode,
                    'hpercode' => $hpercode,
                    'employeeid' => $empid,
                    'token' => $userModel->createToken('diet_tunnel')->plainTextToken,
                    'user_level' => $this->getUserLevel($user->postitle),
                ],
                (array) $user
            );

            // 5. Redirect to frontend with user data
            $redirectUrl = env('APP_FRONTEND_TUNNEL') . '?' . http_build_query($details);
            return redirect($redirectUrl);

        } catch (\Throwable $e) {
            // Log the error and show a friendly page
            \Log::error('Authentication failed', [
                'empid' => $empid,
                'error' => $e->getMessage(),
            ]);

            return view('error');
        }
    }


    public function getUserLevel($position)
    {

        $user_levels = $this->users_repository->getUserLevels();

        $position = strtolower($position);

        foreach ($user_levels as $user_level) {
            if (str_contains($position, $user_level->position)) {
                return $user_level->user_level;
            }
        }

        return -1;
    }

}