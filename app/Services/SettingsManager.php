<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Services;

use Illuminate\Database\Eloquent;
use Illuminate\Support\Collection;
use Tinyissue\Model\Setting;

/**
 * SettingsManager singleton class to maintain a collection of all settings
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property string $key
 */
class SettingsManager extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
        $this->load();
    }

    /**
     * Load all settings
     *
     * @return \Illuminate\Database\Eloquent\Collection|boolean
     */
    protected function load()
    {
        if ($this->count() === 0) {
            // Skip exception if table does not exists
            // This method called from RouteServiceProvider, which is called within command line 'artisan migrate'
            // before the table exists.
            try {
                $items = Setting::all();
                $this->items = is_array($items) ? $items : $this->getArrayableItems($items);
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this;
    }

    /**
     * Returns a setting value
     *
     * @param $name
     * @param mixed|null $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if ($this->load()) {
            foreach ($this->all() as $setting) {
                if ($setting->key === $name) {
                    return $setting->value;
                }
            }
        }

        return $default;
    }

    /**
     * Whether or not the public projects enabled
     *
     * @return bool
     */
    public function isPublicProjectsEnabled()
    {
        return (boolean)$this->get('enable_public_projects') === true;
    }

    /**
     * Save a collection of settings
     *
     * @param $values
     * @return boolean
     */
    public function save($values)
    {
        foreach ($values as $name => $value) {
            $settings = new Setting();
            $setting = $settings->where('key', '=', $name)->first();
            if ($setting) {
                $setting->value = $value;
                $setting->save();
            }
            unset($settings, $setting);
        }

        // Reload items
        $this->items = [];
        $this->load();

        return true;
    }
}
