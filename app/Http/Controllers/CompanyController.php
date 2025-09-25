<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function getCompanyNames()
    {
        return $this->companyService->getCompanies();
    }

    public function index()
    {
        return view('pages.company.index');
    }

    public function edit(Request $request)
    {
           $user = Auth::user();
        if (!$user) {
            return null; // no authenticated user
        }

        $company = Company::with('credential')->where('email', $user->email)->first();

        $companyId = $company->id;



        $company = $this->companyService->_find($companyId);
        if ($request->ajax())
            return $company;
        return view('pages.selected_company.company_profile', compact('company'));
    }

    public function store(StoreCompanyRequest $request)
    {
        return $this->companyService->_create($request->except('logo'));
    }

    public function update(UpdateCompanyRequest $request)
    {

        // dd($request);

        return $this->companyService->_update($request->companyId, $request->except(['logo', 'companyId']));
    }

    public function companyList()
    {
        return $this->companyService->getCompanyList();
    }

    public function destroy($companyId)
    {
        return $this->companyService->_delete($companyId);
    }

    public function selectCompany($companyId)
    {
        session(['selectedCompanyId' => $companyId]);
    }
}
