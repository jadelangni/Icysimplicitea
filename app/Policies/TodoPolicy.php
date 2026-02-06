<?php

namespace App\Policies;

use App\Models\Todo;
use App\Models\User;

class TodoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Todo $todo): bool
    {
        // Admins can view all todos
        if ($user->isAdmin()) {
            return true;
        }

        // Users can view todos they created or are assigned to
        return $user->id === $todo->user_id || $user->id === $todo->assigned_to;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Todo $todo): bool
    {
        // Admins can update all todos
        if ($user->isAdmin()) {
            return true;
        }

        // Users can update todos they created or are assigned to
        return $user->id === $todo->user_id || $user->id === $todo->assigned_to;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Todo $todo): bool
    {
        // Only admins or the creator can delete
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $todo->user_id;
    }
}
