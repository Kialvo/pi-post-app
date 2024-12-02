<?php
namespace App\Services;

use App\Models\User;

class UserService
{
    public function createUser($data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
        ]);
    }

    public function updateUser(User $user, array $data)
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = bcrypt($data['password']);
        }

        return $user->update($updateData);
    }


    public function deleteUser(User $user)
    {
        return $user->delete();
    }

    public function getUsers()
    {
        return User::query();
    }
}
