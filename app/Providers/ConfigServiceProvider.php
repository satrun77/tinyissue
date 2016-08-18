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
 * ConfigServiceProvider is for defining & overriding configurations.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Overwrite any vendor / package configuration.
     *
     * This service provider is intended to provide a convenient location for you
     * to overwrite any "vendor" or package configuration that you may want to
     * modify before the application handles the incoming request / command.
     */
    public function register()
    {
        config([
            'tinyissue.release_date'   => '00-00-0000',
            'tinyissue.version'        => '3.0.0',
            'tinyissue.uploads_dir'    => env('APP_UPLOAD_DIR', 'uploads'),
            'tinyissue.supported_lang' => $this->getLanguages(),
        ]);
    }

    /**
     * Get available languages from translations folder.
     *
     * @return array
     */
    public function getLanguages()
    {
        $languages = [];

        $cdir = scandir(__DIR__ . '/../../resources/lang');
        foreach ($cdir as $value) {
            if (!in_array($value, ['.', '..'])) {
                $languages[$value] = $value;
            }
        }

        return $languages;
    }
}
