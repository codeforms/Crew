<?php
namespace CodeForms\Repositories\Crew\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use CodeForms\Repositories\Crew\Providers\PermissionSwitcher;
/**
 * 
 */
class CrewServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * @return void
     */
    public function boot(PermissionSwitcher $switcher)
    {
        Blade::directive('role', function ($role) 
        {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });

        Blade::directive('endrole', function () 
        {
            return '<?php endif; ?>';
        });

        $switcher->registerPermissions();
    }
}
