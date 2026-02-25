<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('view_users');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, User $model)
    {
        // Users can view their own profile
        if ($user->id === $model->id) {
            return true;
        }
        
        return $user->isAdmin() || $user->hasPermission('view_users');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('create_users');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        // Users can update their own basic info
        if ($user->id === $model->id) {
            return true;
        }
        
        // Only admins can update other users
        return $user->isAdmin() || $user->hasPermission('update_users');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, User $model)
    {
        // Users cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }
        
        // Only admins can delete users
        return $user->isAdmin() || $user->hasPermission('delete_users');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, User $model)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, User $model)
    {
        return $user->isAdmin();
    }
    
    /**
     * Determine whether the user can change the password of the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function changePassword(User $user, User $model)
    {
        // Users can change their own password
        if ($user->id === $model->id) {
            return true;
        }
        
        // Only admins can change other users' passwords
        return $user->isAdmin();
    }
    
    /**
     * Determine whether the user can view activities of the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewActivities(User $user, User $model)
    {
        // Users can view their own activities
        if ($user->id === $model->id) {
            return true;
        }
        
        // Only admins can view other users' activities
        return $user->isAdmin() || $user->hasPermission('view_user_activities');
    }
    
    /**
     * Determine whether the user can view sales performance dashboard.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewSalesPerformance(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('view_sales_performance');
    }
}
