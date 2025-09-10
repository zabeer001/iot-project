@extends('layout.master')

@section('title')
    @parent
    Areas
@endsection

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet"/>
@endpush

@push('')
    <style>
    </style>
@endpush

@section('content')
    @include('pages.area.includes.add_area_modal')
    <div class="card">
        <div class="card-body">
            <div class="mb-5">
                <h5>Area</h5>
                <button type="button" class="btn btn-danger float-end" data-bs-toggle="modal"
                        data-bs-target="#add_area_modal">Add Area
                </button>
            </div>


            <div class="table-responsive">
                <table id="area_table" class="table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Icon</th>
                        <th>Staff Members</th>
                        <th>Temp Objects</th>
                        <th>Activate</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('custom-scripts')
    <script>
        $(document).ready(function () {

            $('#area_table').DataTable({
                pageLength: 10,
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{route('setup.areaList')}}",
                    type: "GET"
                },
                columns: [
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'icon',
                        name: 'icon'
                    },
                    {
                        data: 'staff_members',
                        name: 'staff_members',
                    },
                    {
                        data: 'temp_objects',
                        name: 'temp_objects'
                    },
                    {
                        data: 'activate',
                        name: 'activate'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]

            })
        })

    </script>
@endpush
