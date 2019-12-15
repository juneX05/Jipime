<?php

namespace App\Providers;

/**
 * ServiceProvider
 *
 * The service provider for the modules. After being registered
 * it will make sure that each of the modules are properly loaded
 * i.e. with their routes, views etc.
 *
 * @author kundan Roy <query@programmerlab.com>
 * @package App\Core
 */

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{

    /**
     * Will make sure that the required modules have been fully loaded
     * @return void routeModule
     */
    protected $USER_MODULES_DIRECTORY = 'modules';

    public function boot()
    {
        // For each of the registered modules, include their routes and Views

        if(!is_dir(base_path($this->USER_MODULES_DIRECTORY))){
            File::makeDirectory(base_path($this->USER_MODULES_DIRECTORY), 0777, true, true);
        }

        $user_modules = File::directories(base_path($this->USER_MODULES_DIRECTORY));

        foreach ($user_modules as $module)
        {
            $this->registerFiles($module,'user');
        }

    }

    protected function getModuleDirectoryName($module_path)
    {
        $directory = preg_replace('/\\\|\//',' ',$module_path);
        $directories = explode(' ',$directory);
        $module_directory_position = count($directories) - 1;
        return $directories[$module_directory_position];
    }

    protected function registerFiles($module_path,$type)
    {
        // Load the helper files for each of the modules

        $directory_name = $this->getModuleDirectoryName($module_path);
        $helper_file = $module_path.'/Helpers/Helper.php';
        $web_route_file = $module_path.'/Routes/web.php';
        $api_route_file = $module_path.'/Routes/api.php';

        $views_path = $module_path.'/Views';
        $migrations_path = $module_path.'/Migrations';

        $moduleWEBNamespace = 'Modules\\'.$directory_name.'\Controllers\WEB';
        $moduleAPINamespace = 'Modules\\'.$directory_name.'\Controllers\API';

        if (file_exists($helper_file)) {
            include $helper_file;
        }

        //Load the web routes for each of the modules
        if (file_exists($web_route_file)) {

            Route::middleware('web')
                ->namespace($moduleWEBNamespace)
                ->group($web_route_file);
            //include base_path('app/Core/'.$module.'/Routes/web.php');
        }

        //Load the api routes for each of the modules
        if (file_exists($api_route_file)) {

            Route::prefix('api')
                ->middleware('api')
                ->namespace($moduleAPINamespace)
                ->group($api_route_file);
            //include base_path('app/Core/'.$module.'/Routes/web.php');
        }

        //Load the views
        if (is_dir($views_path)) {
            $this->loadViewsFrom($views_path, strtolower($directory_name));
        }

        $this->loadMigrationsFrom($migrations_path);

    }

}
