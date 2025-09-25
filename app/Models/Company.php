<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'companies';
    protected $fillable = ['name', 'email', 'phone', 'companySize', 'city', 'country', 'postalCode', 'logo', 'address', 'isActive'];



    public function credential()
    {
        return $this->hasOne(CompanyCredential::class);
    }
}
