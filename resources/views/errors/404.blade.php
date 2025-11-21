@extends('errors::layout')

@section('title', 'صفحه پیدا نشد')
@section('code', '404')
@section('message', 'متاسفیم، صفحه‌ای که به دنبال آن هستید وجود ندارد یا حذف شده است.')
@section('neon-color-class', 'text-neon-blue')

@section('background-image', asset('assets/images/backgrounds/bg-404.jpg'))
@section('illustration', asset('assets/images/backgrounds/404.png'))
