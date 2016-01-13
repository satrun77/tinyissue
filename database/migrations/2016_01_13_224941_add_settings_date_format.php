<?php

class AddSettingsDateFormat extends AddSettingsPublicProjects
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
}
