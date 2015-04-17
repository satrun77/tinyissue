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
        'app.js'
    ];
    mix
        .less([
            'base.less',
            'app.less',
            'error.less',
            'login.less',
            'tokenfield.less'
        ], 'resources/css')

        .styles([
            'base.css',
            'tokenfield.css',
            'app.css'
        ], 'public/css/tiny.css')

        .styles([
            'error.css'
        ], 'public/css/tiny_error.css')

        .styles([
            'base.css',
            'login.css'
        ], 'public/css/tiny_login.css')

        .scripts(baseJs, 'public/js/tiny.js')

        .scripts(['project.js'].concat(baseJs), 'public/js/tiny_project.js')

        .scripts([
            'upload/jquery.iframe-transport.js',
            'upload/vendor/jquery.ui.widget.js',
            'upload/jquery.fileupload.js',
            'upload/jquery.fileupload-process.js', 'project.js'
        ].concat(baseJs), 'public/js/tiny_project_issue.js')

        .scripts(['project-new.js'].concat(baseJs), 'public/js/tiny_project_new.js')

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
