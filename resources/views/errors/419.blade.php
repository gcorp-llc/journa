@extends('errors::layout')

@section('title', __('errors.419.title'))
@section('code', '419')
@section('message', __('errors.419.message'))
@section('neon-color-class', 'text-neon-yellow')

{{-- مسیر تصویر پس‌زمینه را با تصویر دلخواه خود جایگزین کنید --}}
@section('background-image', asset('assets/images/backgrounds/bg-404.jpg'))
{{-- مسیر تصویر شناور را با تصویر دلخواه خود جایگزین کنید --}}
@section('illustration', asset('assets/images/backgrounds/419.png'))
