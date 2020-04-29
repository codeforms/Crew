<?php
namespace CodeForms\Repositories\Crew\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * 
 */
class Permission extends Model
{
	/**
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'description'
    ];

    /**
     * 
     */
	public function roles() 
	{
	   return $this->belongsToMany(Role::class,'role_permissions');   
	}

	/**
	 * 
	 */
	public function users() 
	{
	   return $this->belongsToMany(config('auth.providers.users.model'),'user_permissions');    
	}
}
