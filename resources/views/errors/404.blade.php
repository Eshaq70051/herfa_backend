@extends('errors.layout')

@section('title', 'الصفحة غير موجودة')
@section('code', '404')
@section('icon')
    <i class="fa-solid fa-triangle-exclamation"></i>
@endsection
@section('message', 'عذراً، الصفحة غير موجودة')
@section('description', 'الصفحة أو الرابط الذي تحاول الوصول إليه غير متوفر أو تم نقله.')
