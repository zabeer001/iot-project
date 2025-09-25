@extends('layout.master')

@section('title')
    @parent
    Profile
@endsection

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
@endpush

@push('custom-styles')
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="mb-5">
                <h5>Profile</h5>
            </div>

            <form id="edit_company_form" action="{{route('companies.update')}}">
                <input type="hidden" value="{{$company->id}}" id="companyId" name="companyId">
                <div class="row mb-3">
                    <div class="col-6">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" value="{{$company->name}}" id="edit_name" name="name">
                    </div>
                    <div class="col-6">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{$company->email}}" id="edit_email"
                               name="email">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label for="edit_phone" class="form-label">Phone</label>
                        <input type="number" class="form-control" value="{{$company->phone}}" id="edit_phone"
                               name="phone">
                    </div>
                    <div class="col-6">
                        <label for="edit_companySize" class="form-label">Company Size</label>
                        <input type="number" class="form-control" value="{{$company->companySize}}"
                               id="edit_companySize"
                               name="companySize">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label for="edit_city" class="form-label">City</label>
                        <input type="text" class="form-control" value="{{$company->city}}" id="edit_city" name="city">
                    </div>
                    <div class="col-6">
                        <label for="edit_country" class="form-label">Country</label>
                        <input type="text" class="form-control" value="{{$company->country}}" id="edit_country"
                               name="country">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label for="edit_postalCode" class="form-label">Postal Code</label>
                        <input type="text" class="form-control" value="{{$company->postalCode}}" id="edit_postalCode"
                               name="postalCode">
                    </div>

                    <div class="col-6">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="edit_logo" name="logo">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="form-label" for="isActive">Status</label>
                    <div style="margin-left: 13px" class="col-6 form-check form-switch">
                        <input type="checkbox" class="form-check-input"
                               {{$company->isActive === 1 ? 'checked' : ''}} id="isActive" name="isActive">
                        <label class="form-label" for="isActive">(Activate / Deactivate)</label>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea rows="5" cols="8" class="form-control" id="edit_address"
                                  name="address">{{$company->address}}</textarea>
                    </div>
                </div>

                <button type="button" class="btn btn-primary me-2 update_company">Save</button>
            </form>

        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script>
        $(document).ready(function () {

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
                        $this.html('Save')
                        window.scrollTo(
                            {
                                top: 0,
                                behavior: 'smooth'
                            });
                    },
                    success: function () {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Profile saved successfully',
                            showConfirmButton: false,
                            timer: 1000
                        })
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
        })
    </script>
@endpush
