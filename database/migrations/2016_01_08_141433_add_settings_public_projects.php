<?php

use Illuminate\Database\Migrations\Migration;
use Tinyissue\Model\Setting;

class AddSettingsPublicProjects extends Migration
{
    /**
     * List of settings to insert.
     *
     * @var array
     */
    protected $data = [
        'enable_public_projects' => [
            'name'  => 'enable_public_projects',
            'value' => false,
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
                'name'  => $row['name'],
                'value' => $row['value'],
                'key'   => $key,
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
            $setting  = $settings->where('key', '=', $key)->first();
            if ($setting) {
                $setting->delete();
            }
            unset($settings);
        }
    }
}
