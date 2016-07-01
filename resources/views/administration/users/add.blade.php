@extends('layouts.wrapper')

@section('scripts')
    {!! Html::script(elixir('js/tiny_user_form.js')) !!}
@stop

@section('nav/dashboard/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.add_user')
@stop

@section('headingSubTitle')
    @lang('tinyissue.add_new_user')
@stop

@section('content')

 {!! Form::form($form, ['action'=>'','secure'=>null]) !!}

@stop
