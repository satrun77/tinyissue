@extends('email.base')

@section('header')
    <p style="margin:10px 0 0 0;margin-top:0">
        You have been set up with <b>Tiny Issue</b> at {{ URL::to('') }}.
    </p>
@stop

@section('body')
    <tr>
        <td @mailattrs(cell| bgcolor=#ffffff| style=border-left:1px solid #cccccc;border-right:1px solid #cccccc;border-top:0;border-bottom:0;padding:0 15px 0 16px;background-color:#ffffff;border-bottom:none;padding-bottom:0)>
            <table @mailattrs(table| width=100%| bgcolor=#ffffff| style=font-family:Arial,sans-serif;font-size:14px;line-height:20px)>
                <tr>
                    <td width="100%" style="padding:0px;border-collapse:collapse;padding:0 0 10px 0">
                        <p style="margin:10px 0 0 0;margin-top:0">
                            You may log in with email {{ $email }} and password {{ $password }}.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
@stop
