@extends('email.partials.row-base')

@section('content_rows')
    <tr>
        <td style="padding:0px;border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;padding-top:10px">
            <a href="{{ $project->to() }}" @mailattrs(link)>{{ $project->name }}</a>
            @if ($issue)
                / <a href="{{ $issue->to() }}" @mailattrs(link)>#{{ $issue->id }}</a>
            @endif
        </td>
    </tr>
    @if ($issue)
        <tr>
            <td style="vertical-align:top;padding:0px;border-collapse:collapse;padding-right:5px;font-size:20px;line-height:30px">
            <span style="font-family:Arial,sans-serif;padding:0;font-size:20px;line-height:30px;vertical-align:middle">
                <a href="{{ $issue->to() }}" @mailattrs(link)>{{ $issue->title }}</a>
            </span>
            </td>
        </tr>
    @endif
@overwrite
