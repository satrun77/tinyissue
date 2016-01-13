<?php

use Illuminate\Database\Migrations\Migration;
use Tinyissue\Model\Setting;

class AddSettingsDateFormat extends Migration
{
    /**
     * List of settings to insert
     *
     * @var array
     */
    protected $data = [
        'date_format' => [
            'name' => 'date_format',
            'value' => 'F jS \a\t g:i A',
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->data as $key => $row) {
            $settings = new Setting();
            $settings->fill([
                'name' => $row['name'],
                'value' => $row['value'],
                'key' => $key,
            ])->save();
            unset($settings);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->data as $key => $row) {
            $settings = new Setting();
            $setting = $settings->where('key', '=', $key)->first();
            if ($setting) {
                $setting->delete();
            }
            unset($settings);
        }
    }
}
