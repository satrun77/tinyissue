<?php

return [
    'table' => function (array $override) {
        $bgColor = data_get($override, 'bgcolor', '#f5f5f5');

        return [
            'border'      => 0,
            'cellpadding' => 0,
            'cellspacing' => 0,
            'style'       => 'border-collapse:collapse;background-color:' . $bgColor,
            'bgcolor'     => $bgColor,
        ];
    },
    'cell'  => function (array $override) {
        $width = data_get($override, 'width');
        $valign = data_get($override, 'valign', 'top');
        $style = data_get($override, 'style', '');
        $baseStyle = 'font-family:Arial,sans-serif;font-size:14px;line-height:20px;padding:0px;border-collapse:collapse;vertical-align:' . $valign . ';';
        if ($width) {
            $baseStyle .= 'width:' . $width . ';';
        }

        return ['valign' => $valign, 'width' => $width, 'style' => $baseStyle . $style];
    },
    'image' => ['border' => 0, 'style' => 'border-radius:3px;vertical-align:top'],
    'link'  => [
        'target' => '_blank',
        'style'  => 'color:#3b73af;color:#3b73af;text-decoration:none',
    ],
];
