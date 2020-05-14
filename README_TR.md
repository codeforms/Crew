# Crew
Laravel tabanlı yapılar için basit ve hafif kullanıcı grupları ve yetkileri sistemi.

[![GitHub license](https://img.shields.io/github/license/codeforms/Crew)](https://github.com/codeforms/Crew/blob/master/LICENSE)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/codeforms/Crew)
[![stable](http://badges.github.io/stability-badges/dist/stable.svg)](https://github.com/codeforms/Crew/releases)

## Kurulum
* Migration dosyasını kullanarak veri tabanı için gerekli tabloları oluşturun;
``` php artisan migrate```
* Laravel'in app.php config dosyasının ```providers``` alanına aşağıdaki satırı ekleyin;
```php
<?php
'providers' => [
	...
	...
	CodeForms\Repositories\Crew\Providers\CrewServiceProvider::class,
	...
]
```
* app/Http/Kernel.php dosyasındaki ```routeMiddleware``` bölümüne aşağıdaki satırları ekleyin.
```php
<?php
protected $routeMiddleware = [
	...
	'role'       => \CodeForms\Repositories\Crew\Middleware\RoleMiddleware::class,
	'permission' => \CodeForms\Repositories\Crew\Middleware\PermissionMiddleware::class,
	...
];
```
* Son olarak app/User.php model dosyanıza Crew yapısına ait CrewTrait dosyasını ekleyin;
```php
<?php
namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use CodeForms\Repositories\Crew\Traits\CrewTrait;

class User extends Authenticatable
{
    use Notifiable, CrewTrait;
```

## Kullanım
> Aşağıda kullanılan rol ve yetki isimleri sadece örnektir. CRUD işlemleri ile kendi oluşturduğunuz rol ve yetki isimlerini kullanmalısınız. CRUD işlemleri için ```Models``` dizininde yer alan ```Role``` ve ```Permission``` model dosyalarını kullanınız.

#### Rol ve Yetki Sorgulama
Tüm sorgulama metotları her zaman bool (true/false) döner. Aşağıdaki örnekler PHP kodları içinde kullanılır.
```php
<?php
/**********************
 * Kullanıcı işlemleri
 */
$user = User::find(1);
// hasRole ile tek bir kullanıcı rolü veya
// array içinde birden fazla roller de sorgulanabilir.
$user->hasRole('Admin');
$user->hasRole(['Admin', 'Editor']);

// hasPermission ile tek bir yetki veya 
// array içinde birden fazla yetkiler de sorgulanabilir.
$user->hasPermission('edit-post');
$user->hasPermission(['edit-post', 'delete-post']);

# bir kullanıcıya rol atama 
$user->setRole('Admin');
$user->setRole(['User', 'Customer']); // array olarak çoklu atama

# bir kullanıcıya yetkiler atama
$user->setPermission('edit-post');
$user->setPermission(['edit-post', 'delete-post', 'upload']); // array olarak çoklu atama

/**********************
 * Rol işlemleri
 */
$role = Role::find(1);
// bir rolün sahip olduğu yetkiler de aynı şekilde sorgulanabilir.
$role->hasPermission('edit-post');
$role->hasPermission(['edit-post', 'delete-post']);

# bir role yetki(ler) atama
$role->setPermission('edit-post');
$role->setPermission(['edit-post', 'delete-post', 'upload']); // array olarak çoklu atama
```
#### Blade dosyalarında rol ve yetki sorgulama
Laravel'in blade şablon dosyalarında da rol ve yetki sorgulaması kolaylıkla yapılabilir. 
```blade
@role('Editor')
	Bu alanı sadece Editor rolüne sahip olan kullanıcılar görebilir.
@else 
	Bu alanı editörler göremez ancak editör dışındaki diğer rollere sahip olanlar görebilir.
@endrole

@role(['Editor', 'Admin'])
	Bu bölümü sadece Editor ve Admin rolüne sahip olanlar görebilir.
@endrole
```
Blade dosyaları içinde yetki sorgulamak için Laravel'in varsayılan @can direktifi kullanılabilir.
```blade
@can('edit-post')
	'edit-post' yetkisine sahip olanlar görebilir.
@endcan
``` 

#### Rota(route) dosyalarında role ve yetkilerin kullanımı
Crew yapısı sayesinde rol ve yetkiler aynı zamanda rotalarda da kullanılabilir. Yetki veya roller, rotanın ```middleware``` alanında belirtilir. Birden fazla rol ve yetki belirtmek istediğimizde, her bir rol ve yetki arasına '\|' dik çizgi (pipe) işareti konulur.
```php
<?php
/**********************
 * Yetkiler
 */
# aşağıdaki 'admin' sayfasına sadece 'dashboard 'yetkisine
# sahip olan kullanıcılar veya bu yetkiye sahip roller erişebilir
Route::get('admin', 'DashboardController@index')->middleware('permission:dashboard');

# aşağıdaki içerik düzenleme sayfasına sadece 'dashboard' ve 'edit-post'
# yetkisine sahip kullanıcılar veya bu yetkiye sahip roller erişebilir.
Route::get('admin/post/{id}', 'DashboardController@edit')->middleware('permission:dashboard|edit-post');

/**********************
 * Roller
 */
# Admin rolüne sahip olanlar bu sayfaya erişebilir 
Route::get('admin/users', 'BackendUserController@index')->middleware('role:Admin');

# Admin ve Editor rolüne sahip olanlar bu sayfaya erişebilir
Route::get('admin/posts', 'BackendPostController@index')->middleware('role:Admin|Editor');
```
 #### Rota grupları için middleware tanımlaması.
 Bir rota grubu için tanımlanan rol veya yetkiler, grup içinde tanımlanacak olan tüm rotalar için geçerli olur. Böylece her bir rota için ayrı ayrı middleware tanımlaması yapılmaz.
 ```php
 <?php
Route::group([
	'prefix'     => 'admin/dashboard',
	'middleware' => 'role:Admin|Editor'
], function () 
{
	Route::get('posts', 'BackendPostController@index');
	Route::get('posts/{id}', 'BackendPostController@edit');
	...
/**********************
 * Rota grupları için middleware'de 'yetki' tanımlama
 */
Route::group([
	'prefix'     => 'admin/dashboard',
	'middleware' => 'permission:edit-post|delete-post'
], function () 
{
	Route::get('posts', 'BackendPostController@index');

	# aşağıdaki rotaya, üstteki rota grubunda belirtilen 'edit-post' ve 'delete-post'
	# yetkilerine sahip olmanın yanında, ayrıca 'upload' yetkisi olanlar girebilir.
	# Yani bir kullanıcı, rota grubunda belirtilen yetkilere (edit-post, delete-post) 
	# sahip olsa bile, eğer 'upload' yetkisine sahip değilse bu rotaya erişemez.
	Route::get('posts/{id}', 'BackendPostController@edit')->middleware('permission:upload');

	# bu rotaya, üstteki rota grubunda belirtilen yetkilere sahip olmanın yanında, 
	# ayrıca 'Admin' rolüne sahip olanlar erişebilir. Yani bir kullanıcı, rota grubunda 
	# belirtilen yetkilere (edit-post, delete-post) sahip olsa bile, eğer Admin değilse 
	# bu rotaya erişemez.
	Route::post('posts/{id}/delete', 'BackendPostController@delete')->middleware('role:Admin');
	...
```

## Rol ve Yetki oluşturma işlemleri
```php
<?php
# CodeForms\Repositories\Crew\Models\Role;
# Yeni rol oluşturma
Role::create([
	'name' => 'Admin',
	'slug' => 'admin'
]);
# Rol düzenleme
Role::where('id', $role_id)->update([
	'name' => 'Site yöneticisi',
	'slug' => 'manager'
]);
# Yetki silme
Role::destroy($role_id);
Role::destroy([1,2,3]); // birden fazla role id'ler ile silme

# CodeForms\Repositories\Crew\Models\Permission;
# Yeni yetki oluşturma
Permission::create([
	'name' => 'Yönetim ekranına erişim',
	'slug' => 'dashboard'
]);
# Yetki düzenleme
Permission::where('id', $permission_id)->update([
	'name' => 'İçerik düzenleme yetkisi',
	'slug' => 'edit-content'
]);
# Yetki silme
Permission::destroy($permission_id);
Permission::destroy([1,2,3]); // birden fazla permission id'ler ile silme
```