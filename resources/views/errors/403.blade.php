@extends('errors.layout')

@section('title', 'غير مصرح بالدخول')
@section('code', '403')
@section('icon')
    <i class="fa-solid fa-lock"></i>
@endsection
@section('message', 'وصول غير مصرح به')
@section('description', 'ليس لديك الصلاحيات الكافية للوصول إلى هذا القسم أو تنفيذ هذه العملية.')
