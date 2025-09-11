<?php

namespace App\Services;

use App\Models\Company;
use Carbon\Carbon;

class CompanyService extends BaseService
{
    public function __construct()
    {
        $this->model(Company::class);
    }

    public function getCompanies()
    {
        return $this->_model()->select('id', 'name')->orderBy('name')->get();
    }

    public function getCompanyList()
    {
        $companies = $this->_all();
        return datatables()->of($companies)
            ->addColumn('created_date', function ($row) {
                return Carbon::parse($row->created_at)->format('d.m.Y H:i');
            })
            ->addColumn('modules', function ($row) {
                return $row->modules;
            })
            ->addColumn('activate', function ($row) {
                if ($row->isActive === 1) {
                    $text = 'Yes';
                    $textClass = 'text-success';
                } else {
                    $text = 'No';
                    $textClass = 'text-danger';
                }
                return '<span class="' . $textClass . '">' . $text . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '<button data-company-id="' . $row->id . '" type="button" class="btn btn-danger mr_8 edit_company">Edit</button>
                        <button data-company-id="' . $row->id . '" type="button" class="btn btn-inverse-danger btn-icon remove_company"><i data-feather="trash-2"></i></button>';
            })
            ->rawColumns(['created_date', 'modules', 'activate', 'action'])
            ->make(true);
    }
}
