@extends('errors::layout')

@section('title', __('errors.500.title'))
@section('code', '500')
@section('message', __('errors.500.message'))
@section('neon-color-class', 'text-neon-red')

{{-- مسیر تصویر پس‌زمینه را با تصویر دلخواه خود جایگزین کنید --}}
@section('background-image', asset('assets/images/backgrounds/bg-503.jpg'))
{{-- مسیر تصویر شناور را با تصویر دلخواه خود جایگزین کنید --}}
@section('illustration', asset('assets/images/backgrounds/500.png'))
