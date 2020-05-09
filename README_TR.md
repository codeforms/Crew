# Crew
Laravel tabanlı yapılar için basit ve hafif kullanıcı grupları ve yetkileri sistemi.

[![GitHub license](https://img.shields.io/github/license/codeforms/Crew)](https://github.com/codeforms/Crew/blob/master/LICENSE)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/codeforms/Crew)
[![stable](http://badges.github.io/stability-badges/dist/stable.svg)](https://github.com/codeforms/Crew/releases)

> Crew yapısı, rol ve yetki oluşturma/düzenleme/silme vb klasik CRUD işlemleri için metotlar içermez. Laravel'de CRUD işlemlerinin nasıl yapıldığına dair internet üzerinden birçok kaynağa erişilebilir.

### Kurulum
* Migration dosyasını kullanarak veri tabanı için gerekli tabloları oluşturun;
``` php artisan migrate```
* Laravel'in app.php config dosyasının ```providers``` alanına aşağıdaki satırı ekleyin;
```php
'providers' => [
	...
	...
	CodeForms\Repositories\Crew\Providers\CrewServiceProvider::class,
	...
]
```
* app/Http/Kernel.php dosyasındaki ```routeMiddleware``` bölümüne aşağıdaki satırları ekleyin.
```php
protected $routeMiddleware = [
	...
	'role'       => \CodeForms\Repositories\Crew\Middleware\RoleMiddleware::class,
	'permission' => \CodeForms\Repositories\Crew\Middleware\PermissionMiddleware::class,
	...
];
```
* Son olarak app/User.php model dosyanıza Crew yapısına ait CrewTrait dosyasını ekleyin;
```php
namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use CodeForms\Repositories\Crew\Traits\CrewTrait;

class User extends Authenticatable
{
    use Notifiable, CrewTrait;
```

### Kullanım
#### Rol ve Yetki Sorgulama
Tüm sorgulama metotları her zaman bool (true/false) döner. Aşağıdaki örnekler PHP kodları içinde kullanılır.
```php
$user = User::find(1);
// hasRole ile tek bir kullanıcı rolü veya
// array içinde birden fazla roller de sorgulanabilir.
$user->hasRole('Admin');
$user->hasRole(['Admin', 'Editor']);

// hasPermission ile tek bir yetki veya 
// array içinde birden fazla yetkiler de sorgulanabilir.
$user->hasPermission('edit-post');
$user->hasPermission(['edit-post', 'delete-post']);

$role = Role::find(1);
// bir rolün sahip olduğu yetkiler de aynı şekilde sorgulanabilir.
$role->hasPermission('edit-post');
$role->hasPermission(['edit-post', 'delete-post']);
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

#### Rota(route) dosyalarında role ve yetkilerin kullanımı
Crew yapısı sayesinde rol ve yetkiler aynı zamanda rotalarda da kullanılabilir. Yetki veya roller, rotanın ```middleware``` alanında belirtilir. Birden fazla rol ve yetki belirtmek istediğimizde '\|' dik çizgi (pipe) işareti kullanılır. 
```php
/**
 * Yetkiler
 */
# 'admin' sayfasına sadece 'dashboard 'yetkisine
# sahip olan kullanıcılar veya roller erişebilir
Route::get('admin', 'DashboardController@index')->middleware('permission:dashboard');

# aşağıdaki içerik düzenleme sayfasına sadece 'dashboard' ve 'edit-post'
# yetkisine sahip kullanıcılar veya roller erişebilir.
Route::get('admin/post/{id}', 'DashboardController@edit')->middleware('permission:dashboard|edit-post');

/**
 * Roller
 */
# Admin rolüne sahip olanlar bu sayfaya erişebilir 
Route::get('admin/users', 'BackendUserController@index')->middleware('role:Admin');

# Admin ve Editor rolüne sahip olanlar bu sayfaya erişebilir
Route::get('admin/posts', 'BackendPostController@index')->middleware('role:Admin|Editor');
```