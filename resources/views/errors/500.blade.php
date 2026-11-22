@extends('errors.layout')

@section('title', 'خطأ في الخادم')
@section('code', '500')
@section('icon')
    <i class="fa-solid fa-server"></i>
@endsection
@section('message', 'حدث خطأ في الخادم')
@section('description', 'نواجه مشكلة فنية مؤقتة في معالجة طلبك، يرجى المحاولة مرة أخرى لاحقاً.')
