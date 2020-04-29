<?php
namespace CodeForms\Repositories\Crew\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * 
 */
class Role extends Model
{
	/**
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'description',
    ];

    /**
     * 
     */
    public function hasPermission($permission)
    {
        return $this->permissions->contains('slug', $permission);
    }

	/**
	 * 
	 */
    public function permissions() 
    {
	   return $this->belongsToMany(Permission::class, 'role_permissions');  
	}

	/**
	 * 
	 */
	public function users() 
	{
	   return $this->belongsToMany(config('auth.providers.users.model'), 'user_roles');
	}
}
