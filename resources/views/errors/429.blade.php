@extends('errors::layout')

@section('title', __('errors.429.title'))
@section('code', '429')
@section('message', __('errors.429.message'))
@section('neon-color-class', 'text-neon-yellow')

{{-- مسیر تصویر پس‌زمینه را با تصویر دلخواه خود جایگزین کنید --}}
@section('background-image',asset('assets/images/backgrounds/bg-503.jpg'))
{{-- مسیر تصویر شناور را با تصویر دلخواه خود جایگزین کنید --}}
@section('illustration', asset('assets/images/backgrounds/429.png'))
