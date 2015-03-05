@extends('layouts.wrapper')

@section('nav/admin/class')
active
@stop

@section('contentTitle')
    {!! Html::heading('title', ['title' => 'add_tag', 'subTitle' => 'add_new_tag']) !!}
@stop

@section('content')

 {!! Form::form($form, ['action'=>'','secure'=>null]) !!}

@stop
