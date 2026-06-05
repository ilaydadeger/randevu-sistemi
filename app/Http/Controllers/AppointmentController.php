<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nail_tech_id' => 'required|exists:users,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'design_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagePath = null;
        $estimatedPrice = 0;
        if ($request->hasFile('design_image')) {
            $imagePath = $request->file('design_image')->store('appointments', 'public');
            $estimatedPrice = floatval($request->input('estimated_price', 0));
            if ($estimatedPrice <= 0) {
                $estimatedPrice = 500;
            }
        }

        $appointment = \App\Models\Appointment::create([
            'artist_id' => $request->nail_tech_id,
            'client_name' => $request->client_name,
            'client_phone' => $request->client_phone,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'image_path' => $imagePath,
            'estimated_price' => $estimatedPrice,
            'status' => 'pending',
            'tracking_code' => \Illuminate\Support\Str::uuid(),
        ]);

        return redirect()->back()->with([
            'success' => true,
            'tracking_code' => $appointment->tracking_code
        ]);
    }
}
