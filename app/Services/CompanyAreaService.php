<?php

namespace App\Services;

use App\Models\CompanyArea;

class CompanyAreaService extends BaseService
{
    public function __construct()
    {
        $this->model(CompanyArea::class);
    }

    public function getAreaList()
    {
        $areas = $this->_all();
        return datatables()->of($areas)
            ->addColumn('icon', function ($row) {
                return 'N/A';
            })
            ->addColumn('staff_members', function ($row) {
                return 'N/A';
            })
            ->addColumn('temp_objects', function ($row) {
                return 'N/A';
            })
            ->addColumn('activate', function ($row) {
                return 'N/A';
            })
            ->addColumn('action', function ($row) {
                return 'N/A';
            })
            ->rawColumns(['icon', 'staff_members', 'temp_objects', 'activate', 'action'])
            ->make(true);
    }
}
