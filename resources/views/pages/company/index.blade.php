@extends('layout.master')

@section('title')
    @parent
    Companies
@endsection

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
@endpush

@push('custom-styles')
    <style>
        .mr_8 {
            margin-right: 8px;
        }
    </style>
@endpush

@section('content')
    @include('pages.company.includes.add_company_modal')
    @include('pages.company.includes.edit_company_modal')
    <div class="card">
        <div class="card-body">
            <div class="mb-5">
                <h5>Companies</h5>
                <button type="button" class="btn btn-danger float-end" data-bs-toggle="modal"
                        data-bs-target="#add_company_modal">Add Company
                </button>
            </div>


            <div class="table-responsive">
                <table id="company_table" class="table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Created Date</th>
                        <th>Company Size</th>
                        <th>Modules</th>
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
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script>
        $(document).ready(function () {

            // edit company ajax
            $(document).on("click", ".edit_company", function () {
                let url = '{{ route("companies.edit", ":id") }}';
                url = url.replace(':id', $(this).data('companyId'));
                $.ajax({
                    url: url,
                    method: "get",
                    success: function (response) {
                        if (response.isActive === 1)
                            $('#isActive').attr('checked', 'checked');
                        else
                            $('#isActive').removeAttr('checked');
                        $('#edit_address').val(response.address)
                        $('#edit_city').val(response.city)
                        $('#edit_companySize').val(response.companySize)
                        $('#edit_country').val(response.country)
                        $('#edit_email').val(response.email)
                        $('#companyId').val(response.id)
                        $('#edit_name').val(response.name)
                        $('#edit_phone').val(response.phone)
                        $('#edit_postalCode').val(response.postalCode)
                        $('#edit_company_modal').modal('show')
                    },
                    error: function (jqXHR) {
                        alert('Status : ' + jqXHR.status + "\n" + jqXHR.responseText);
                    }
                });
            })

            // update company ajax
            $(document).on("click", ".update_company", function () {

                let form = document.getElementById("edit_company_form");
                let url = form.getAttribute("action");
                let $this = $(this)

                $this.prop("disabled", true);
                $this.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`);

                let isActive = 0;
                if ($("#isActive").is(":checked"))
                    isActive = 1;

                let formData = new FormData(form)
                formData.set('isActive', isActive)

                $(".invalid_input").remove();
                $.ajax({
                    url: url,
                    method: "post",
                    data: formData,
                    processData: false,
                    contentType: false,
                    complete: function () {
                        $this.removeAttr('disabled')
                        $this.html('Update')
                    },
                    success: function () {
                        $('#edit_company_modal').modal('hide')
                        location.reload()
                    },
                    error: function (jqXHR) {
                        if (jqXHR.status === 422) {
                            let errorsArray = Object.entries(jqXHR.responseJSON.errors);

                            $.each(errorsArray, function (index, value) {
                                let formInput = $('#edit_company_form [name="' + value[0] + '"]');
                                let error = '<span class="invalid_input text-danger">' + value[1] + '</span>';
                                formInput.parent().append(error);
                            })
                        } else {
                            alert('Status : ' + jqXHR.status + "\n" + jqXHR.responseText);
                        }
                    }
                });
            });

            // remove company ajax
            $(document).on("click", "button.remove_company", function () {

                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = '{{ route("companies.destroy", ":id") }}';
                        url = url.replace(':id', $(this).data('companyId'));
                        $.ajax({
                            url: url,
                            method: "get",
                            success: function () {
                                destroyDataTable($('#company_table'))
                                initCompanyDataTable()
                            },
                            error: function (jqXHR) {
                                alert('Status : ' + jqXHR.status + "\n" + jqXHR.responseText);
                            }
                        });
                    }
                })
            });

            // add company ajax
            $(document).on("click", ".add_company", function () {
                let form = document.getElementById("add_company_form");
                let url = form.getAttribute("action");
                let $this = $(this)

                $this.prop("disabled", true);
                $this.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`);

                $(".invalid_input").remove();
                $.ajax({
                    url: url,
                    method: "post",
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    complete: function () {
                        $this.removeAttr('disabled')
                        $this.html('Save')
                    },
                    success: function () {
                        $('#add_company_modal').modal('hide')
                        location.reload()
                    },
                    error: function (jqXHR) {
                        if (jqXHR.status === 422) {
                            let errorsArray = Object.entries(jqXHR.responseJSON.errors);

                            $.each(errorsArray, function (index, value) {
                                let formInput = $('#add_company_form [name="' + value[0] + '"]');
                                let error = '<span class="invalid_input text-danger">' + value[1] + '</span>';
                                formInput.parent().append(error);
                            })
                        } else {
                            alert('Status : ' + jqXHR.status + "\n" + jqXHR.responseText);
                        }
                    }
                });
            });

            $('#add_company_modal,#edit_company_modal').on('hidden.bs.modal', function (e) {
                $(".invalid_input").remove();
            })

            function initCompanyDataTable() {
                $('#company_table').DataTable({
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    order: [[3, 'desc']],
                    drawCallback: function (settings) {
                        // place trash icons for delete buttons
                        feather.replace()
                    },
                    ajax: {
                        url: "{{route('companies.companyList')}}",
                        type: "GET"
                    },
                    columns: [
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'phone',
                            name: 'phone'
                        },
                        {
                            data: 'email',
                            name: 'email',
                        },
                        {
                            data: 'created_date',
                            name: 'created_date'
                        },
                        {
                            data: 'companySize',
                            name: 'companySize'
                        },
                        {
                            data: 'modules',
                            name: 'modules'
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
            }

            function destroyDataTable(selector) {
                $(selector).DataTable().destroy()
            }

            initCompanyDataTable();
        })
    </script>
@endpush
