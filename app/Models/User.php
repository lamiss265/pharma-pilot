<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'language',
        'phone',
        'address',
        'position',
        'status',
        'last_login_at',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
    ];

    /**
     * Check if the user is an admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a worker
     *
     * @return bool
     */
    public function isWorker()
    {
        return $this->role === 'worker';
    }

    /**
     * Check if the user is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        // Admins have all permissions
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if the user has the specific permission
        return $this->permissions && in_array($permission, $this->permissions);
    }

    /**
     * Get the sales recorded by this user
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the activities logged by this user
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Get the notifications for this user
     */
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Get the total sales amount by this user
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getTotalSalesAmount($startDate = null, $endDate = null)
    {
        $query = $this->sales();
        
        if ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }
        
        return $query->sum(\DB::raw('quantity * price'));
    }

    /**
     * Get the total number of sales by this user
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return int
     */
    public function getTotalSalesCount($startDate = null, $endDate = null)
    {
        $query = $this->sales();
        
        if ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }
        
        return $query->count();
    }

    /**
     * Log user activity
     *
     * @param string $action
     * @param string $description
     * @param mixed $subject
     * @return UserActivity
     */
    public function logActivity($action, $description, $subject = null)
    {
        $activity = new UserActivity([
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'properties' => $subject ? json_encode($subject->toArray()) : null,
        ]);
        
        $this->activities()->save($activity);
        
        return $activity;
    }

    /**
     * Update last login timestamp
     *
     * @return $this
     */
    public function updateLastLogin()
    {
        $this->last_login_at = now();
        $this->save();
        
        return $this;
    }
}
