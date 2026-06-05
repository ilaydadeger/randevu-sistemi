<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    /**
     * Display the dynamic storefront for a specific nail artist by their slug.
     */
    public function show($slug)
    {
        $nailTech = User::where('role', 'artist')
                        ->where('slug', $slug)
                        ->firstOrFail();

        return view('home', compact('nailTech'));
    }
}
