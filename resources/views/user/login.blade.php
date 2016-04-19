@extends('layouts.login')

@section('content')
<h2 class="form-signin-heading">@lang('tinyissue.login_to_your_account')</h2>
<div  class="form">
    {!! Form::form($form, ['action'=>'signin','secure'=>null]) !!}
</div>
@stop
