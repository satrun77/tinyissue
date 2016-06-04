@extends('email.partials.row-base')

@section('content_rows')
    <tr>
        <td width="100%" style="padding:0px;border-collapse:collapse;padding:0 0 10px 0">
            <p style="text-align:right;padding:0px;border-collapse:collapse;color:#999;font-size:12px;line-height:18px;font-family:Arial,sans-serif;margin:10px 0 0 0;margin-top:0;">
                Dated: {{ Html::date($comment['date']) }}
            </p>

            @if(!empty($comment['now']))
                {!! str_replace('<p>', '<p style="margin:10px 0 0 0;margin-top:0">', $comment['now']) !!}
            @endif

            @if(!empty($comment['was']))
                <div style="background-color:#ffe7e7;text-decoration:line-through">
                    {!! str_replace('<p>', '<p style="margin:10px 0 0 0;margin-top:0">', $comment['was']) !!}
                </div>
            @endif
        </td>
    </tr>
@overwrite
