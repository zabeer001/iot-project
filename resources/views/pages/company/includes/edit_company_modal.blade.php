<!-- Modal -->
<div class="modal fade" id="edit_company_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Company</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body">
                <form id="edit_company_form" action="{{route('companies.update')}}">
                    <input type="hidden" id="companyId" name="companyId">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name">
                        </div>
                        <div class="col-6">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="number" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="col-6">
                            <label for="edit_companySize" class="form-label">Company Size</label>
                            <input type="number" class="form-control" id="edit_companySize" name="companySize">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="edit_city" name="city">
                        </div>
                        <div class="col-6">
                            <label for="edit_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="edit_country" name="country">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit_postalCode" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="edit_postalCode" name="postalCode">
                        </div>

                        <div class="col-6">
                            <label for="logo" class="form-label">Logo</label>
                            <input type="file" class="form-control" id="edit_logo" name="logo">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="form-label" for="isActive">Status</label>
                        <div style="margin-left: 13px" class="col-6 form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" name="isActive">
                            <label class="form-label" for="isActive">(Activate / Deactivate)</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea rows="5" cols="8" class="form-control" id="edit_address"
                                      name="address"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-inverse-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger update_company">Update</button>
            </div>
        </div>
    </div>
</div>
