@extends('errors::layout')

@section('title', __('errors.403.title'))
@section('code', '403')
@section('message', __('errors.403.message'))
@section('neon-color-class', 'text-neon-red')

{{-- مسیر تصویر پس‌زمینه را با تصویر دلخواه خود جایگزین کنید --}}
@section('background-image',  asset('assets/images/backgrounds/bg-404.jpg'))
{{-- مسیر تصویر شناور را با تصویر دلخواه خود جایگزین کنید --}}
@section('illustration', asset('assets/images/backgrounds/403.png'))
