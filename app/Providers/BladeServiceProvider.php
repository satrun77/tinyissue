<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tinyissue\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

/**
 * BladeServiceProvider is the blade service provider for extending blade template engine
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class BladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        \Blade::extend(function ($view) {
            $pattern = '/@(macro)\s*(\([\'|\"](\w+)[\'|\"],\s*(([^\@])+|(.*))\))/xim';

            return preg_replace($pattern, "<?php \$___tiny['\$3']=function(\$4){ ob_start(); ?>\n", $view);
        });

        \Blade::extend(function ($view, BladeCompiler $compiler) {
            $pattern = $compiler->createPlainMatcher('endmacro');

            return preg_replace($pattern, "\n<?php return ob_get_clean();} ?>\n", $view);
        });

        \Blade::extend(function ($view) {
            $pattern = '/@(usemacro)\s*(\([\'|\"](\w+)[\'|\"],\s*(([^\@])+|(.*))\))/xim';

            return preg_replace($pattern, "<?php echo \$___tiny['\$3'](\$4); ?>\n", $view);
        });

        \Blade::extend(function ($view, BladeCompiler $compiler) {
            $pattern = $compiler->createMatcher('permission');

            return preg_replace($pattern, "$1<?php if(Auth::user()->permission$2): ?>\n", $view);
        });

        \Blade::extend(function ($view, BladeCompiler $compiler) {
            $pattern = $compiler->createPlainMatcher('endpermission');

            return preg_replace($pattern, "\n<?php endif; ?>\n", $view);
        });
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     */
    public function register()
    {
    }
}
