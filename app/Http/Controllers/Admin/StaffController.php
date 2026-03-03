<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'admin')
                    ->orderBy('id', 'asc')
                    ->get();

        return view('admin.attendance.staff', compact('users'));
    }

}
