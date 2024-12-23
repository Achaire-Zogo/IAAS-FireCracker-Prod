<?php

namespace App\Http\Controllers;

use App\Models\VmOffer;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $offers = VmOffer::where('is_active', true)
            ->orderBy('cpu_count')
            ->orderBy('memory_size_mib')
            ->get();
            
        return view('welcome', compact('offers'));
    }
}
