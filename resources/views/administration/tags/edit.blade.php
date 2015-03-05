@extends('layouts.wrapper')

@section('nav/admin/class')
active
@stop

@section('contentTitle')
    {!! Html::heading('title', ['title' => 'update_tag', 'subTitle' => 'update_tag_description']) !!}
@stop

@section('content')
  {!! Form::form($form, ['action'=>'','secure'=>null]) !!}
@stop
