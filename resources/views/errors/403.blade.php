@extends('errors::layout')

@section('title', 'دسترسی غیرمجاز')
@section('code', '403')
@section('message', 'شما اجازه دسترسی به این صفحه را ندارید.')
@section('neon-color-class', 'text-neon-red')

@section('background-image', asset('assets/images/backgrounds/bg-404.jpg'))
@section('illustration', asset('assets/images/backgrounds/403.png'))
