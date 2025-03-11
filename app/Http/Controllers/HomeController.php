<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\Customer;
use App\Models\Payment;

class HomeController extends Controller
{
    public function index()
    {
        $countCompany = Company::count();
        $countUser = User::count();
        $countCustomer = Customer::count();
        $countPayment = Payment::count();
        return view('pages.home', compact(
            'countCompany',
            'countUser',
            'countCustomer',
            'countPayment'
        ));
    }

}
