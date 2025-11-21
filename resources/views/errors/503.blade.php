@extends('errors::layout')

@section('title', 'در حال بروزرسانی')
@section('code', '503')
@section('message', 'سایت در حال بروزرسانی است. لطفاً دقایقی دیگر مراجعه کنید.')
@section('neon-color-class', 'text-neon-purple')

@section('background-image', asset('assets/images/backgrounds/bg-503.jpg'))
@section('illustration', asset('assets/images/backgrounds/503.png'))
