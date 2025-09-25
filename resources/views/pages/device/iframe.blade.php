@extends('layout.master')

@section('title')
    @parent
    Devices
@endsection

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
@endpush

@push('custom-styles')


  
@endpush

@section('content')

<iframe src="{{ $url }}" width="100%" height="100%" style="border:1px solid #ccc;"></iframe>
    
@endsection
