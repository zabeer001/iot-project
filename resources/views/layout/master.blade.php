<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Responsive Laravel Admin Dashboard Template based on Bootstrap 5">
    <meta name="author" content="NobleUI">
    <meta name="keywords"
          content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, laravel, theme, front-end, ui kit, web">

    <title>AppnGO - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- End fonts -->

    <!-- CSRF Token -->
    <meta name="_token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">

    <!-- plugin css -->
    <link href="{{ asset('assets/fonts/feather-font/css/iconfont.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet"/>
    <!-- end plugin css -->

@stack('plugin-styles')

<!-- common css -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"/>
    <!-- end common css -->

    @stack('custom-styles')
    <style>
        .navbar .search-form {
            width: 18%;
            position: absolute;
            right: 5rem;
            top: 1rem;
        }
    </style>
</head>
<body data-base-url="{{url('/')}}">

<script src="{{ asset('assets/js/spinner.js') }}"></script>

<div class="main-wrapper" id="app">
    @include('layout.sidebar')
    <div class="page-wrapper">
        @include('layout.header')
        <div class="page-content">
            @yield('content')
        </div>
        @include('layout.footer')
    </div>
</div>

<!-- base js -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('assets/plugins/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<!-- end base js -->

<!-- plugin js -->
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
@stack('plugin-scripts')
<!-- end plugin js -->

<!-- common js -->
<script src="{{ asset('assets/js/template.js') }}"></script>
<!-- end common js -->

<script>
    var sessionBasedCompanyId = @json(session('selectedCompanyId'));

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    // populate all companies dynamically and pre select selected company
    var companyDropdown = $("select.select_company_dropdown").select2()
    $.ajax({
        type: 'get',
        url: '{{route('companies.getCompanyNames')}}'
    }).then(function (data) {
        $.each(data, function (index, value) {
            if (value.id == sessionBasedCompanyId)
                option = new Option(value.name, value.id, true, true);
            else
                option = new Option(value.name, value.id, false, false);
            companyDropdown.append(option);
        })
    });

    // select company dropdown
    $(document).on("change", "select.select_company_dropdown", function () {
        let id = $(this).val();
        let url = '{{ route("companies.select",":id") }}';
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            method: "get",
            success: function () {
                let url = '{{ route('companies.section.profile',":id") }}';
                url = url.replace(':id', id);
                window.location.href = url;
            },
            error: function (jqXHR) {
                alert('Status : ' + jqXHR.status + "\n" + jqXHR.responseText);
            }
        });
    })


</script>
@stack('custom-scripts')
</body>
</html>
