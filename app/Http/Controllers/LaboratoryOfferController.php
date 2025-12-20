<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Laboratory;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LaboratoryOfferController extends Controller
{
    public function accepted(Request $request)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        // Get accepted offers for this laboratory
        $query = Offer::where('laboratory_id', $laboratory->id)
            ->where('request_type', 'test')
            ->where('status', 'accepted')
            ->with([
                'request.client',
                'request.address.area.city.governorate',
                'user'
            ])
            ->orderBy('created_at', 'desc');

        // Filter by search if provided
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('request.client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $offers = $query->with('attachments')->paginate(15);

        return view('laboratories.offers.accepted', compact('offers', 'laboratory'));
    }

    public function updateVendorStatus(Request $request, Offer $offer)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.'
            ], 403);
        }

        // Verify the offer belongs to this laboratory
        if ($offer->laboratory_id != $laboratory->id) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'غير مصرح لك بتعديل هذا العرض.' : 'You are not authorized to update this offer.'
            ], 403);
        }

        $validated = $request->validate([
            'vendor_status' => 'required|in:sample_collected,test_completed',
        ]);

        $offer->update(['vendor_status' => $validated['vendor_status']]);

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'ar' ? 'تم تحديث الحالة بنجاح' : 'Status updated successfully',
            'vendor_status' => $offer->vendor_status
        ]);
    }

    public function uploadAttachment(Request $request, Offer $offer)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.'
            ], 403);
        }

        // Verify the offer belongs to this laboratory
        if ($offer->laboratory_id != $laboratory->id) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'غير مصرح لك برفع الملفات لهذا العرض.' : 'You are not authorized to upload files for this offer.'
            ], 403);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('attachments/offers', $fileName, 'public');

        $attachment = $offer->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'ar' ? 'تم رفع الملف بنجاح' : 'File uploaded successfully',
            'attachment' => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'url' => $attachment->url,
                'mime_type' => $attachment->mime_type,
                'is_image' => $attachment->isImage(),
                'is_pdf' => $attachment->isPdf(),
            ]
        ]);
    }

    public function deleteAttachment(Offer $offer, Attachment $attachment)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.'
            ], 403);
        }

        // Verify the offer belongs to this laboratory
        if ($offer->laboratory_id != $laboratory->id) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'غير مصرح لك بحذف هذا الملف.' : 'You are not authorized to delete this file.'
            ], 403);
        }

        // Verify attachment belongs to this offer
        if ($attachment->attachable_id != $offer->id || $attachment->attachable_type != Offer::class) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'الملف غير مرتبط بهذا العرض.' : 'File is not associated with this offer.'
            ], 403);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'ar' ? 'تم حذف الملف بنجاح' : 'File deleted successfully'
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        // Get all sent offers for this laboratory (all statuses)
        $query = Offer::where('laboratory_id', $laboratory->id)
            ->where('request_type', 'test')
            ->with([
                'request.client',
                'request.address.area.city.governorate',
                'user',
                'medicineLines' => function($q) {
                    $q->with('medicine:id,name');
                },
                'testLines' => function($q) {
                    $q->with('medicalTest:id,test_name_en,test_name_ar');
                }
            ])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by search if provided
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('request.client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $offers = $query->paginate(15);

        return view('laboratories.offers.index', compact('offers', 'laboratory'));
    }

    public function cancel(Request $request, Offer $offer)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        // Verify the offer belongs to this laboratory
        if ($offer->laboratory_id != $laboratory->id) {
            return redirect()->route('laboratories.offers.index')
                ->with('error', app()->getLocale() === 'ar' ? 'غير مصرح لك بإلغاء هذا العرض.' : 'You are not authorized to cancel this offer.');
        }

        // Only allow canceling pending offers
        if ($offer->status != 'pending') {
            return redirect()->route('laboratories.offers.index')
                ->with('error', app()->getLocale() === 'ar' ? 'يمكن إلغاء العروض المعلقة فقط.' : 'Only pending offers can be cancelled.');
        }

        // Update offer status to cancelled/rejected
        $offer->update(['status' => 'rejected']);

        return redirect()->route('laboratories.offers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إلغاء العرض بنجاح' : 'Offer cancelled successfully');
    }
}

