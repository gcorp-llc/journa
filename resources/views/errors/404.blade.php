@extends('errors::layout')

@section('title', __('errors.404.title'))
@section('code', '404')
@section('message', __('errors.404.message'))
@section('neon-color-class', 'text-neon-blue')

{{-- مسیر تصویر پس‌زمینه را با تصویر دلخواه خود جایگزین کنید --}}
@section('background-image', asset('assets/images/backgrounds/bg-404.jpg'))
{{-- مسیر تصویر شناور را با تصویر دلخواه خود جایگزین کنید --}}
@section('illustration', asset('assets/images/backgrounds/404.png'))
