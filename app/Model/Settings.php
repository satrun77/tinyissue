<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * User is model class for users
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property string $key
 */
class Settings extends Model
{
    const ENABLE = 1;
    const DISABLE = 0;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'name', 'value'];

    /**
     * Collection of all settings
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $settings = null;

    /**
     * Load all settings
     *
     * @return \Illuminate\Database\Eloquent\Collection|boolean
     */
    protected function loadSettings()
    {
        if (null === $this->settings) {
            // Skip exception if table does not exists
            // This method called from RouteServiceProvider, which is called within command line 'artisan migrate'
            // before the table exists.
            try {
                $this->settings = static::all();
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this->settings;
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
        if ($this->loadSettings()) {
            foreach ($this->settings as $setting) {
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
}
