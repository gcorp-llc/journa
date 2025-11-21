@extends('errors::layout')

@section('title', 'درخواست بیش از حد')
@section('code', '429')
@section('message', 'تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً چند لحظه صبر کنید.')
@section('neon-color-class', 'text-neon-yellow')

@section('background-image', asset('assets/images/backgrounds/bg-503.jpg'))
@section('illustration', asset('assets/images/backgrounds/429.png'))
