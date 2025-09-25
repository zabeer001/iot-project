<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCredential extends Model
{
    use HasFactory;
    
    protected $fillable = ['company_id', 'url', 'accessKey', 'secretKey'];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
