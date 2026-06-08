<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SmsService;

class NailTechController extends Controller
{
    public function preview()
    {
        $nailTech = auth()->user();
        return view('home', compact('nailTech'));
    }

    public function appointments()
    {
        $user = auth()->user();
        
        $pendingAppointments = $user->appointments()
                                    ->where('status', 'pending')
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                                    
        $now = now();

        // 1. Sıradaki Randevular: Status is approved and in the future
        $upcomingAppointments = $user->appointments()
                                     ->where('status', 'approved')
                                     ->where(function($query) use ($now) {
                                         $query->where('appointment_date', '>', $now->toDateString())
                                               ->orWhere(function($q) use ($now) {
                                                   $q->where('appointment_date', $now->toDateString())
                                                     ->where('appointment_time', '>', $now->toTimeString());
                                               });
                                     })
                                     ->orderBy('appointment_date', 'asc')
                                     ->orderBy('appointment_time', 'asc')
                                     ->get();
                                     
        // 2. Tamamlanan Randevular: Status is completed OR (status is approved and in the past)
        $completedAppointments = $user->appointments()
                                      ->where(function($query) use ($now) {
                                          $query->where('status', 'completed')
                                                ->orWhere(function($q) use ($now) {
                                                    $q->where('status', 'approved')
                                                      ->where(function($sub) use ($now) {
                                                          $sub->where('appointment_date', '<', $now->toDateString())
                                                              ->orWhere(function($sub2) use ($now) {
                                                                  $sub2->where('appointment_date', $now->toDateString())
                                                                        ->where('appointment_time', '<=', $now->toTimeString());
                                                              });
                                                      });
                                                });
                                      })
                                      ->orderBy('appointment_date', 'desc')
                                      ->orderBy('appointment_time', 'desc')
                                      ->get();

        // 3. İptal Edilen Randevular: Status is cancelled
        $cancelledAppointments = $user->appointments()
                                      ->where('status', 'cancelled')
                                      ->orderBy('appointment_date', 'desc')
                                      ->orderBy('appointment_time', 'desc')
                                      ->get();
                                     
        $scheduleBlocks = $user->scheduleBlocks()->get();
        $notes = $user->notes()->orderBy('created_at', 'desc')->get();

        return view('appointments', compact(
            'pendingAppointments', 
            'upcomingAppointments', 
            'completedAppointments',
            'cancelledAppointments',
            'scheduleBlocks',
            'notes'
        ));
    }

    public function book()
    {
        $user = auth()->user();
        
        $categories = \App\Models\ServiceCategory::where('name', '!=', 'Düz Renk')
            ->get()
            ->groupBy('group_name');

        $order = [
            'Temel İşlem' => 1,
            'Nail Art Karmaşıklığı' => 2,
            'Uzunluk' => 3,
            'Şekil' => 4,
        ];

        $categories = $categories->sortBy(function ($value, $key) use ($order) {
            return $order[$key] ?? 99;
        });

        $userPrices = $user->userPrices->keyBy('service_category_id');
        
        return view('book', compact('categories', 'userPrices'));
    }

    public function profile()
    {
        $user = auth()->user();
        
        $todayAppointments = $user->appointments()
                                  ->whereDate('appointment_date', today())
                                  ->where('status', 'approved')
                                  ->orderBy('appointment_time', 'asc')
                                  ->get();
                                  
        $pendingApprovals = $user->appointments()
                                 ->where('status', 'pending')
                                 ->orderBy('created_at', 'desc')
                                 ->get();
                                 
        return view('profile', compact('user', 'todayAppointments', 'pendingApprovals'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'nullable|string|max:255',
            'salon_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:5120', // 5MB max
            'portfolio_image_1' => 'nullable|image|max:5120',
            'portfolio_image_2' => 'nullable|image|max:5120',
            'portfolio_image_3' => 'nullable|image|max:5120',
            'show_portfolio' => 'nullable',
        ]);
        
        $user->name = $request->name;
        $user->salon_name = $request->salon_name;
        $user->bio = $request->bio;
        $user->show_portfolio = $request->boolean('show_portfolio');
        
        // Handle profile photo removal
        if ($request->boolean('remove_profile_photo')) {
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
                $user->profile_photo_path = null;
            }
        } elseif ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }
            
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }
        
        // Handle portfolio images
        for ($i = 1; $i <= 3; $i++) {
            $field = "portfolio_image_{$i}";
            $removeField = "remove_portfolio_image_{$i}";
            
            if ($request->boolean($removeField)) {
                if ($user->$field) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->$field);
                    $user->$field = null;
                }
            } elseif ($request->hasFile($field)) {
                if ($user->$field) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->$field);
                }
                $user->$field = $request->file($field)->store('portfolio', 'public');
            }
        }
        
        $user->save();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Profil başarıyla güncellendi.', 'user' => $user]);
        }
        
        return back()->with('success', 'Profil güncellendi.');
    }
    
    public function updatePricing(Request $request)
    {
        $user = auth()->user();
        $prices = $request->input('prices', []); // [service_category_id => price]
        
        foreach ($prices as $categoryId => $price) {
            $priceValue = floatval($price) > 0 ? floatval($price) : 0;
            
            \App\Models\UserPrice::updateOrCreate(
                ['artist_id' => $user->id, 'service_category_id' => $categoryId],
                ['price' => $priceValue]
            );
        }
        
        $user->exclude_length_pricing = $request->boolean('exclude_length_pricing');
        $user->exclude_shape_pricing = $request->boolean('exclude_shape_pricing');
        $user->save();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Fiyatlarınız başarıyla güncellendi.']);
        }
        
        return back()->with('success', 'Sabit fiyat modeliniz başarıyla güncellendi.');
    }

    public function updateAppointmentStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,cancelled,completed',
            'price' => 'nullable|numeric|min:0',
        ]);

        $appointment = \App\Models\Appointment::findOrFail($id);

        if ($appointment->artist_id !== auth()->id()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $previousStatus = $appointment->status;
        $newStatus      = $request->status;

        // Save price if present in request
        if ($request->has('price') && !is_null($request->price)) {
            $appointment->estimated_price = $request->price;
        }

        $appointment->status = $newStatus;
        $appointment->save();

        // ── SMS Gönderimi ─────────────────────────────────────────────────
        // Durum değişikliği onay veya iptal ise müşteriye SMS gönder.
        // client_phone, Appointment modelindeki 'encrypted' cast sayesinde
        // burada otomatik olarak çözülmüş (açık metin) hâlde gelir.
        if (in_array($newStatus, ['approved', 'cancelled']) && $previousStatus !== $newStatus) {
            $nailTech  = auth()->user();
            $salonName = $nailTech->salon_name ?? $nailTech->name ?? 'Salon';
            $smsService = new SmsService();

            if ($newStatus === 'approved') {
                $smsService->sendApproval(
                    phone:      $appointment->client_phone,  // cast: otomatik çözülmüş
                    clientName: $appointment->client_name,
                    salonName:  $salonName,
                    price:      floatval($appointment->estimated_price)
                );
            } elseif ($newStatus === 'cancelled') {
                $smsService->sendRejection(
                    phone:      $appointment->client_phone,  // cast: otomatik çözülmüş
                    clientName: $appointment->client_name,
                    salonName:  $salonName
                );
            }
        }
        // ─────────────────────────────────────────────────────────────────

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Randevu durumu güncellendi.']);
        }

        return back()->with('success', 'Randevu durumu güncellendi.');
    }

    public function updateAppointmentPrice(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $appointment = \App\Models\Appointment::findOrFail($id);

        if ($appointment->artist_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }

        $appointment->update(['estimated_price' => $request->price]);

        return response()->json([
            'success' => true,
            'message' => 'Fiyat başarıyla güncellendi.',
            'estimated_price' => $appointment->estimated_price
        ]);
    }

    public function toggleScheduleBlock(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        $user = auth()->user();
        $date = $request->date;
        $time = $request->time . ':00'; // Append seconds for DB format match

        $existingBlock = $user->scheduleBlocks()
                              ->where('blocked_date', $date)
                              ->where('blocked_time', $time)
                              ->first();

        if ($existingBlock) {
            $existingBlock->delete();
            return response()->json(['success' => true, 'status' => 'available', 'message' => 'Saat kullanıma açıldı.']);
        } else {
            $user->scheduleBlocks()->create([
                'blocked_date' => $date,
                'blocked_time' => $time,
            ]);
            return response()->json(['success' => true, 'status' => 'blocked', 'message' => 'Saat kapatıldı.']);
        }
    }

    public function toggleDayBlock(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'action' => 'required|in:block,open',
        ]);

        $user = auth()->user();
        $date = $request->date;
        $hours = $user->work_hours ?? ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

        if ($request->action === 'block') {
            foreach ($hours as $hour) {
                $time = $hour . ':00';
                $user->scheduleBlocks()->firstOrCreate([
                    'blocked_date' => $date,
                    'blocked_time' => $time,
                ]);
            }
            return response()->json(['success' => true, 'status' => 'blocked', 'message' => 'Gündeki tüm saatler kapatıldı.']);
        } else {
            $user->scheduleBlocks()->where('blocked_date', $date)->delete();
            return response()->json(['success' => true, 'status' => 'open', 'message' => 'Gündeki tüm saatler açıldı.']);
        }
    }

    public function saveDayBlocks(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'blocked_hours' => 'nullable|array',
            'blocked_hours.*' => 'string|regex:/^[0-2][0-9]:[0-5][0-9]$/',
        ]);

        $user = auth()->user();
        $date = $request->date;
        $blockedHours = $request->input('blocked_hours', []);

        // Delete all existing schedule blocks for that date
        $user->scheduleBlocks()->where('blocked_date', $date)->delete();

        // Create new schedule blocks for the given date matching blocked_hours
        foreach ($blockedHours as $hour) {
            $time = $hour . ':00';
            $user->scheduleBlocks()->create([
                'blocked_date' => $date,
                'blocked_time' => $time,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Değişiklikler başarıyla kaydedildi.',
        ]);
    }

    public function updateWorkHours(Request $request)
    {
        $request->validate([
            'work_hours' => 'nullable|array',
            'work_hours.*' => ['required', 'string', 'regex:/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/'],
        ]);

        $user = auth()->user();
        $hours = $request->input('work_hours', []);
        sort($hours);
        
        $user->work_hours = $hours;
        $user->save();

        return back()->with('success', 'Çalışma saatleriniz güncellendi.');
    }

    public function getRealtimeUpdates()
    {
        $user = auth()->user();
        $now = now();
        
        // Pending approvals
        $pendingApprovals = $user->appointments()
                                 ->where('status', 'pending')
                                 ->orderBy('created_at', 'desc')
                                 ->get()
                                 ->map(function($a) {
                                     return [
                                         'id' => $a->id,
                                         'client_name' => $a->client_name,
                                         'client_phone' => $a->client_phone,
                                         'date_formatted' => \Carbon\Carbon::parse($a->appointment_date)->locale('tr')->translatedFormat('d M, Y'),
                                         'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                                         'price' => floatval($a->estimated_price),
                                         'image_url' => $a->image_path ? asset('storage/' . $a->image_path) : null,
                                     ];
                                 });

        // Today's approved appointments
        $todayAppointments = $user->appointments()
                                  ->whereDate('appointment_date', today())
                                  ->where('status', 'approved')
                                  ->orderBy('appointment_time', 'asc')
                                  ->get()
                                  ->map(function($a) {
                                      return [
                                          'id' => $a->id,
                                          'client_name' => $a->client_name,
                                          'client_phone' => $a->client_phone,
                                          'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                                          'price' => floatval($a->estimated_price),
                                          'image_url' => $a->image_path ? asset('storage/' . $a->image_path) : null,
                                      ];
                                  });

        // Upcoming appointments
        $upcomingAppointments = $user->appointments()
                                     ->where('status', 'approved')
                                     ->where(function($query) use ($now) {
                                         $query->where('appointment_date', '>', $now->toDateString())
                                               ->orWhere(function($q) use ($now) {
                                                   $q->where('appointment_date', $now->toDateString())
                                                     ->where('appointment_time', '>', $now->toTimeString());
                                               });
                                     })
                                     ->orderBy('appointment_date', 'asc')
                                     ->orderBy('appointment_time', 'asc')
                                     ->get()
                                     ->map(function($a) {
                                         return [
                                             'id' => $a->id,
                                             'price' => floatval($a->estimated_price),
                                             'date' => $a->appointment_date,
                                             'date_formatted' => strtoupper(\Carbon\Carbon::parse($a->appointment_date)->locale('tr')->translatedFormat('d M')),
                                             'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                                             'client_name' => $a->client_name,
                                             'tracking_code_short' => substr($a->tracking_code, 0, 4),
                                             'image_url' => $a->image_path ? asset('storage/' . $a->image_path) : null,
                                         ];
                                     });

        // Completed appointments
        $completedAppointments = $user->appointments()
                                      ->where(function($query) use ($now) {
                                          $query->where('status', 'completed')
                                                ->orWhere(function($q) use ($now) {
                                                    $q->where('status', 'approved')
                                                      ->where(function($sub) use ($now) {
                                                          $sub->where('appointment_date', '<', $now->toDateString())
                                                              ->orWhere(function($sub2) use ($now) {
                                                                  $sub2->where('appointment_date', $now->toDateString())
                                                                        ->where('appointment_time', '<=', $now->toTimeString());
                                                              });
                                                      });
                                                });
                                      })
                                      ->orderBy('appointment_date', 'desc')
                                      ->orderBy('appointment_time', 'desc')
                                      ->get()
                                      ->map(function($a) {
                                          return [
                                              'id' => $a->id,
                                              'price' => floatval($a->estimated_price),
                                              'date' => $a->appointment_date,
                                              'date_formatted' => strtoupper(\Carbon\Carbon::parse($a->appointment_date)->locale('tr')->translatedFormat('d M')),
                                              'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                                              'client_name' => $a->client_name,
                                              'tracking_code_short' => substr($a->tracking_code, 0, 4),
                                              'image_url' => $a->image_path ? asset('storage/' . $a->image_path) : null,
                                          ];
                                      });

        // Cancelled appointments
        $cancelledAppointments = $user->appointments()
                                      ->where('status', 'cancelled')
                                      ->orderBy('appointment_date', 'desc')
                                      ->orderBy('appointment_time', 'desc')
                                      ->get()
                                      ->map(function($a) {
                                          return [
                                              'id' => $a->id,
                                              'price' => floatval($a->estimated_price),
                                              'date' => $a->appointment_date,
                                              'date_formatted' => strtoupper(\Carbon\Carbon::parse($a->appointment_date)->locale('tr')->translatedFormat('d M')),
                                              'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                                              'client_name' => $a->client_name,
                                              'tracking_code_short' => substr($a->tracking_code, 0, 4),
                                              'image_url' => $a->image_path ? asset('storage/' . $a->image_path) : null,
                                          ];
                                      });

        // Blocked slots
        $blockedSlots = new \stdClass();
        $scheduleBlocks = $user->scheduleBlocks()->get();
        foreach($scheduleBlocks as $block) {
            $key = $block->blocked_date . '_' . substr($block->blocked_time, 0, 5);
            $blockedSlots->$key = true;
        }

        // Occupied slots
        $occupiedSlots = new \stdClass();
        $activeBookings = $user->appointments()
            ->whereIn('status', ['pending', 'approved'])
            ->where('appointment_date', '>=', today()->toDateString())
            ->get();
        foreach ($activeBookings as $appt) {
            $key = $appt->appointment_date . '_' . substr($appt->appointment_time, 0, 5);
            $occupiedSlots->$key = true;
        }

        // Notes
        $notesList = $user->notes()
                          ->orderBy('created_at', 'desc')
                          ->get()
                          ->map(function($n) {
                              return [
                                  'id' => $n->id,
                                  'title' => $n->title,
                                  'content' => $n->content,
                                  'date_formatted' => \Carbon\Carbon::parse($n->created_at)->locale('tr')->translatedFormat('d M, Y'),
                              ];
                          });

        return response()->json([
            'success' => true,
            'pendingApprovals' => $pendingApprovals,
            'todayAppointments' => $todayAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'completedAppointments' => $completedAppointments,
            'cancelledAppointments' => $cancelledAppointments,
            'blockedSlots' => $blockedSlots,
            'occupiedSlots' => $occupiedSlots,
            'notes' => $notesList,
        ]);
    }

    public function storeNote(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $note = auth()->user()->notes()->create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Not başarıyla kaydedildi.',
            'note' => [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'date_formatted' => \Carbon\Carbon::parse($note->created_at)->locale('tr')->translatedFormat('d M, Y'),
            ]
        ]);
    }

    public function updateNote(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $note = auth()->user()->notes()->findOrFail($id);
        $note->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Not başarıyla güncellendi.',
            'note' => [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'date_formatted' => \Carbon\Carbon::parse($note->created_at)->locale('tr')->translatedFormat('d M, Y'),
            ]
        ]);
    }

    public function deleteNote($id)
    {
        $note = auth()->user()->notes()->findOrFail($id);
        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Not başarıyla silindi.'
        ]);
    }
}
