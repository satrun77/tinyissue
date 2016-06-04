@extends('email.partials.row-base')

@section('content_rows')
    <tr>
        <td>
            <table @mailattrs(table| bgcolor=#ffffff)>
                @foreach ($changes as $label => $change)
                    @if (!isset($change['noLabel']) || !$change['noLabel'])
                    <tr>
                        <th style="color:#707070;font:normal 14px/20px Arial,sans-serif;text-align:left;vertical-align:top;padding:2px 0">
                            {{ trans('tinyissue.' . $label) }}:
                        </th>
                        <td style="padding:0px;border-collapse:collapse;font:normal 14px/20px Arial,sans-serif;padding:2px 0 2px 5px;vertical-align:top">
                            @if (!empty($change['url']))
                                <a href="{{ $change['url'] }}" @mailattrs(link)>{{ $change['now'] or $change }}</a>
                            @else
                                @if (!empty($change['was']))
                                    <span style="background-color:#ffe7e7;text-decoration:line-through">{{ $change['was'] }}</span>
                                @endif

                                @if ($label === 'body')
                                    <div style="background-color:#ddfade">{!! $change['now'] or $change !!}</div>
                                @else
                                    <span style="background-color:#ddfade">{{ $change['now'] or $change }}</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endif
                @endforeach
            </table>
        </td>
    </tr>
@overwrite
