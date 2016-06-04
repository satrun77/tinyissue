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

/**
 * BladeServiceProvider is the blade service provider for extending blade template engine.
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
        \Blade::directive(
            'macro',
            function ($expression) {
                $pattern = '/(\([\'|\"](\w+)[\'|\"],\s*(([^\@])+|(.*))\))/xim';
                $matches = [];
                preg_match_all($pattern, $expression, $matches);

                if (!isset($matches[3][0])) {
                    throw new \InvalidArgumentException(sprintf('Invalid arguments in blade: macro%s', $expression));
                }

                return sprintf("<?php \$___tiny['%s']=function(%s)use(\$__env){ ob_start(); ?>\n", $matches[2][0], $matches[3][0]);
            }
        );

        \Blade::directive(
            'endmacro',
            function () {
                return "\n<?php return ob_get_clean();} ?>\n";
            }
        );

        \Blade::directive(
            'usemacro',
            function ($expression) {
                $pattern = '/(\([\'|\"](\w+)[\'|\"],\s*(([^\@])+|(.*))\))/xim';
                $matches = [];
                preg_match_all($pattern, $expression, $matches);

                if (!isset($matches[3][0])) {
                    throw new \InvalidArgumentException(sprintf('Invalid arguments in blade: usemacro%s', $expression));
                }

                return sprintf("<?php echo \$___tiny['%s'](%s); ?>\n", $matches[2][0], $matches[3][0]);
            }
        );

        \Blade::directive(
            'permission',
            function ($expression) {
                return "<?php if(!Auth::guest() && Auth::user()->permission{$expression}): ?>";
            }
        );

        \Blade::directive(
            'endpermission',
            function () {
                return '<?php endif; ?>';
            }
        );

        \Blade::directive(
            'mailattrs',
            function ($expression) {
                // Get parameters
                $params = array_map('trim', explode('|', trim($expression, '()')));
                // Get style based on the first parameter
                $style = config('mailcss.' . $params[0]);

                if (!$style) {
                    return '';
                }

                // Convert the parameters starting from second into key => value array
                $override = array_reduce(array_slice($params, 1), function ($override, $param) {
                    $segments = array_map('trim', explode('=', $param));
                    $override[$segments[0]] = $segments[1];

                    return $override;
                }, []);

                // Style can be callback or an array. Merge and echo.
                if (is_callable($style)) {
                    $style = $style($override);
                } elseif (isset($params[1])) {
                    $style = array_merge($style, $override);
                }

                return "<?php echo '" . \Html::attributes($style) . "'; ?>";
            }
        );
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
