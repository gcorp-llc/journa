@extends('errors::layout')

@section('title', 'نشست منقضی شد')
@section('code', '419')
@section('message', 'نشست کاربری شما منقضی شده است. لطفاً صفحه را رفرش کرده و مجدداً تلاش کنید.')
@section('neon-color-class', 'text-neon-yellow')

@section('background-image', asset('assets/images/backgrounds/bg-404.jpg'))
@section('illustration', asset('assets/images/backgrounds/419.png'))
