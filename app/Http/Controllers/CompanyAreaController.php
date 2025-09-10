<?php

namespace App\Http\Controllers;

use App\Services\CompanyAreaService;

class CompanyAreaController extends Controller
{
    protected $companyAreaService;

    public function __construct(CompanyAreaService $companyAreaService)
    {
        $this->companyAreaService = $companyAreaService;
    }

    public function index()
    {
        return view('pages.area.index');
    }

    public function areaList()
    {
        return $this->companyAreaService->getAreaList();
    }
}
