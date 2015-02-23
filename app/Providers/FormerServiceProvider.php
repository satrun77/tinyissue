<?php

namespace Tinyissue\Providers;

class FormerServiceProvider extends \Former\FormerServiceProvider
{
    /**
     * Register the HTML builder instance.
     */
    public function register()
    {
        parent::register();

        $this->app['former.dispatcher']->addRepository('Tinyissue\\Form\Former\\Fields\\');

        // Stop it from rendering field name into label
        \Former::setOption('automatic_label', false);
    }
}
