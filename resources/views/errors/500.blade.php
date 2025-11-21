@extends('errors::layout')

@section('title', 'خطای سرور')
@section('code', '500')
@section('message', 'مشکلی در سرور رخ داده است. ما در حال بررسی هستیم.')
@section('neon-color-class', 'text-neon-red')

@section('background-image', asset('assets/images/backgrounds/bg-503.jpg'))
@section('illustration', asset('assets/images/backgrounds/500.png'))
