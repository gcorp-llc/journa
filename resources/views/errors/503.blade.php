@extends('errors::layout')

@section('title', __('errors.503.title'))
@section('code', '503')
@section('message', __('errors.503.message'))
@section('neon-color-class', 'text-neon-purple')

{{-- مسیر تصویر پس‌زمینه را با تصویر دلخواه خود جایگزین کنید --}}
@section('background-image', asset('assets/images/backgrounds/bg-503.jpg'))
{{-- مسیر تصویر شناور را با تصویر دلخواه خود جایگزین کنید --}}
@section('illustration', asset('assets/images/backgrounds/503.png'))
