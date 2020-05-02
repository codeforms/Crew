<?php
namespace CodeForms\Repositories\Crew\Models;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use CodeForms\Repositories\Crew\Traits\PermissionTrait;
/**
 * 
 */
class Role extends Model
{
    /**
     * 
     */
    use PermissionTrait;

	/**
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'description',
    ];

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
