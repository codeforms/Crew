# Crew
Laravel tabanlı yapılar için basit ve hafif kullanıcı grupları ve yetkileri sistemi.

[![GitHub license](https://img.shields.io/github/license/codeforms/Crew)](https://github.com/codeforms/Crew/blob/master/LICENSE)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/codeforms/Crew)
[![stable](http://badges.github.io/stability-badges/dist/stable.svg)](https://github.com/codeforms/Crew/releases)

## Kurulum
* Migration dosyasını kullanarak veri tabanı için gerekli tabloları oluşturun;
``` php artisan migrate```
* Laravel'in **app.php** config dosyasının ```providers``` alanına **ayrıca** aşağıdaki satırı ekleyin;
```php
<?php
'providers' => [
	CodeForms\Repositories\Crew\Providers\CrewServiceProvider::class,
]
```
* **app/Http/Kernel.php** dosyasındaki ```routeMiddleware``` bölümüne **ayrıca** aşağıdaki satırları ekleyin.
```php
<?php
protected $routeMiddleware = [
	'role'       => \CodeForms\Repositories\Crew\Middleware\RoleMiddleware::class,
	'permission' => \CodeForms\Repositories\Crew\Middleware\PermissionMiddleware::class,
];
```
* Son olarak ```app/User.php``` model dosyanıza **CrewTrait** dosyasını ekleyin;
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
#### Rol ve yetki oluşturma
Rol ve yetkiler, klasik CRUD işlemleriyle oluşturulur. CRUD işlemleri için ```Models``` dizininde yer alan **Role** ve **Permission** model dosyaları kullanılmalıdır.

> Role ve Yetki oluştururken kaydedilecek olan '**slug**' verisi, tüm sorgu işlemleri için birincil ölçüttür.
 
##### Rol oluşturma/düzenleme/silme
```php
<?php
# Yeni rol oluşturma
Role::create([
	'name' => 'Site Yöneticisi',
	'slug' => 'Admin'
]);
# Rol düzenleme
Role::where('id', $role_id)->update([
	'name' => 'Site Editörü',
	'slug' => 'Editor'
]);
# Rol silme
Role::destroy($role_id);
Role::destroy([1,2,3]); // birden fazla role id'ler ile silme
```
##### Yetki oluşturma/düzenleme/silme
```php
<?php
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

> Aşağıda kullanılan rol ve yetki isimleri sadece örnektir. CRUD işlemleri ile kendi oluşturduğunuz rol ve yetki isimlerini kullanmalısınız. Rol ve yetki sorgulamaları için '**slug**' bilgisi ölçüt alınır.

#### Rol ve Yetki Sorgulama
Tüm sorgulama metotları her zaman bool (true/false) döner. Aşağıdaki örnekler PHP kodları içinde kullanılır.

##### Kullanıcı(lar) için rol ve yetki sorgulama;
```php
<?php
$user = User::find(1);

# bir kullanıcı için rol(ler) sorgulama
$user->hasRole('Admin');
$user->hasRole(['Admin', 'Editor']);

# bir kullanıcı için yetki(ler) sorgulama
$user->hasPermission('edit-post');
$user->hasPermission(['edit-post', 'delete-post']);
```
##### Kullanıcılar için yetki ve rol atama işlemleri;
```php
<?php
# bir kullanıcıya rol atama 
$user->setRole('site-yoneticisi');
$user->setRole(['kullanici', 'musteri']); // array olarak çoklu atama

# bir kullanıcıya yetkiler atama
$user->setPermission('edit-post');
$user->setPermission(['edit-post', 'delete-post', 'upload']); // array olarak çoklu atama
```
##### Roller için yetki sorgulama ve atama işlemleri;
```php
<?php
$role = Role::find(1);

# bir rol için yetki sorgulama
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

##### Rotalar için middleware'de 'permission' kullanımı;

```php
<?php 
# aşağıdaki 'admin' sayfasına sadece 'dashboard 'yetkisine
# sahip olan kullanıcılar veya bu yetkiye sahip roller erişebilir
Route::get('admin', 'DashboardController@index')->middleware('permission:dashboard');

# aşağıdaki içerik düzenleme sayfasına sadece 'dashboard' ve 'edit-post'
# yetkisine sahip kullanıcılar veya bu yetkiye sahip roller erişebilir.
Route::get('admin/post/{id}', 'DashboardController@edit')->middleware('permission:dashboard|edit-post');
```

##### Rotalar için middleware'de 'role' kullanımı;

```php
# Bu rotaya sadece 'Admin' rolüne sahip olanlar erişebilir 
Route::get('admin/users', 'BackendUserController@index')->middleware('role:Admin');

# Bu rotaya sadece Admin veya Editor rolüne sahip olanlar erişebilir
Route::get('admin/posts', 'BackendPostController@index')->middleware('role:Admin|Editor');
```
#### Rota grupları için middleware tanımlaması.
Bir rota grubu için tanımlanan rol veya yetkiler, grup içinde tanımlanacak olan tüm rotalar için geçerli olur. Böylece her bir rota için ayrı ayrı middleware tanımlaması yapılmaz.

##### Rota grupları için middleware'de rol tanımlama;
```php
<?php
Route::group([
	'prefix'     => 'admin/dashboard',
	'middleware' => 'role:Admin|Editor'
], function () 
{
	/**
	* Aşağıdaki rotalara sadece Admin ve Editor rollerine
	* sahip kullanıcılara erişebilir.
	*/
	Route::get('posts', 'BackendPostController@index');
	Route::get('posts/{id}', 'BackendPostController@edit');
});
```
##### Rota grupları için middleware'de yetki tanımlama;
```php
<?php
Route::group([
	'prefix'     => 'admin/dashboard',
	'middleware' => 'permission:edit-post|delete-post'
], function () 
{
	Route::get('posts', 'BackendPostController@index');

	# bir kullanıcı, rota grubunda belirtilen yetkilere (edit-post, delete-post) 
	# sahip olsa bile, eğer bu rotada ek olarak belirtilen 'upload' yetkisine sahip değilse
	# bu rotaya erişemez.
	Route::get('posts/{id}', 'BackendPostController@edit')->middleware('permission:upload');

	# bir kullanıcı, rota grubunda belirtilen yetkilere (edit-post, delete-post) 
	# sahip olsa bile, eğer 'Admin' değilse bu rotaya erişemez.
	Route::post('posts/{id}/delete', 'BackendPostController@delete')->middleware('role:Admin');
});
```