<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nail_tech_id'     => 'required|exists:users,id',
            'client_name'      => 'required|string|max:255',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'design_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagePath = null;
        $estimatedPrice = 0;
        if ($request->hasFile('design_image')) {
            $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
            $upload = $cloudinary->uploadApi()->upload($request->file('design_image')->getRealPath(), [
                'folder' => 'appointments'
            ]);
            $imagePath = $upload['secure_url'];
            $estimatedPrice = floatval($request->input('estimated_price', 0));
            if ($estimatedPrice <= 0) {
                $estimatedPrice = 500;
            }
        }

        $appointment = \App\Models\Appointment::create([
            'artist_id'        => $request->nail_tech_id,
            'client_name'      => $request->client_name,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'image_path'       => $imagePath,
            'estimated_price'  => $estimatedPrice,
            'status'           => 'pending',
            'tracking_code'    => \Illuminate\Support\Str::uuid(),
        ]);

        // Randevu takip sayfasına yönlendir
        return redirect()->route('appointment.track', ['tracking_code' => $appointment->tracking_code]);
    }

    public function track(string $tracking_code)
    {
        $appointment = \App\Models\Appointment::with('artist')
            ->where('tracking_code', $tracking_code)
            ->firstOrFail();

        if (request()->wantsJson()) {
            return response()->json([
                'status'          => $appointment->status,
                'estimated_price' => $appointment->estimated_price,
            ]);
        }

        return view('randevu-takip', compact('appointment'));
    }
}
