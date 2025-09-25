<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class Credentials
{
    public static function getCredentials()
    {
        $user = Auth::user();
        if (!$user) {
            return null; // no authenticated user
        }

        $company = Company::with('credential')->where('email', $user->email)->first();

        if (!$company) {
            return null; // company not found
        }

        return $company->credential;
    }
}
