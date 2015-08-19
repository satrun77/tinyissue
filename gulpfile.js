var elixir = require('laravel-elixir');
elixir.config.sourcemaps = false;

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    var baseJs = [
        'jquery.js',
        'jquery.cookie.js',
        'jquery-ui.js',
        'jquery.tokenfield.js',
        'slideout.min.js',
        'bootstrap.js',
        'app.js'
    ];
    mix
        .less([
            'base.less',
            'app.less',
            'tokenfield.less'
        ], 'resources/css/app.css')

        .less([
            'base.less',
            'login.less'
        ], 'resources/css/login.css')

        .less([
            'base.less',
            'error.less'
        ], 'resources/css/error.css')

        .styles([
            'app.css'
        ], 'public/css/tiny.css', 'resources/css')

        .styles([
            'error.css'
        ], 'public/css/tiny_error.css', 'resources/css')

        .styles([
            'login.css'
        ], 'public/css/tiny_login.css', 'resources/css')

        .scripts(baseJs, 'public/js/tiny.js', 'resources/js')

        .scripts(baseJs.concat(['project.js']), 'public/js/tiny_project.js', 'resources/js')

        .scripts(baseJs.concat([
            'upload/jquery.iframe-transport.js',
            'upload/vendor/jquery.ui.widget.js',
            'upload/jquery.fileupload.js',
            'upload/jquery.fileupload-process.js',
            'project.js'
        ]), 'public/js/tiny_project_issue.js', 'resources/js')

        .scripts(baseJs.concat(['project-new.js']), 'public/js/tiny_project_new.js', 'resources/js')

        .version([
            'css/tiny.css',
            'css/tiny_error.css',
            'css/tiny_login.css',
            'js/tiny.js',
            'js/tiny_project.js',
            'js/tiny_project_issue.js',
            'js/tiny_project_new.js'
        ])

        .copy('./resources/assets/fonts', 'public/build/fonts')
        .copy('./resources/assets/images', 'public/images')
    ;
});
