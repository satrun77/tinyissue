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

/**
 * FormerServiceProvider for extending Former service provider
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
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
