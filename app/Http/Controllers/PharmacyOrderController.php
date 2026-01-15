<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Notifications\OrderPaidNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PharmacyOrderController extends Controller
{
	public function index(Request $request)
	{
		$user = Auth::user();
		$pharmacy = Pharmacy::find($user->pharmacy_id);
		
		if (!$pharmacy) {
			return redirect()->route('admin.dashboard')
				->with('error', app()->getLocale() === 'ar' 
					? 'أنت غير مرتبط بأي صيدلية.' 
					: 'You are not associated with any pharmacy.');
		}

		$query = Order::where('pharmacy_id', $pharmacy->id)
			->with(['request.client', 'user', 'offer'])
			->orderByDesc('created_at');

		// Filter by status if provided
		if ($request->filled('status')) {
			$query->where('status', $request->string('status'));
		}

		// Filter by payment status if provided
		if ($request->filled('paid')) {
			$query->where('payed', $request->boolean('paid'));
		}

		// Search by order ID or client name/phone
		if ($request->filled('search')) {
			$term = $request->string('search');
			$query->where(function ($q) use ($term) {
				$q->where('id', 'like', "%{$term}%")
					->orWhereHas('request.client', function ($qq) use ($term) {
						$qq->where('name', 'like', "%{$term}%")
						   ->orWhere('phone_number', 'like', "%{$term}%");
					});
			});
		}

		$orders = $query->paginate(15);

		return view('pharmacies.orders.index', compact('orders', 'pharmacy'));
	}

	public function updateStatus(Request $request, Order $order)
	{
		$user = Auth::user();
		$pharmacy = Pharmacy::find($user->pharmacy_id);

		if (!$pharmacy || $order->pharmacy_id !== $pharmacy->id) {
			return redirect()->route('pharmacies.orders.index')
				->with('error', app()->getLocale() === 'ar' 
					? 'غير مصرح لك بتعديل هذا الطلب.' 
					: 'You are not authorized to update this order.');
		}

		$validated = $request->validate([
			'status' => 'required|in:preparing,delivering,delivered',
		]);

		// Store old status before updating
		$oldStatus = $order->status;
		$newStatus = $validated['status'];

		// Only update if status actually changed
		if ($oldStatus !== $newStatus) {
			// Update the order status
			$order->update(['status' => $newStatus]);
			
			// Refresh and load necessary relationships after updating
			$order->refresh();
			$order->load(['request.client', 'pharmacy']);

			// Send notification to client
			try {
				if ($order->request && $order->request->client) {
					$client = $order->request->client;
					$client->notify(new OrderStatusUpdatedNotification($order, $oldStatus, $newStatus));
				}
			} catch (\Exception $e) {
				// Log error but don't fail the request
				Log::error('Failed to send order status notification: ' . $e->getMessage());
			}
		}

		return redirect()->route('pharmacies.orders.index')
			->with('success', app()->getLocale() === 'ar' 
				? 'تم تحديث حالة الطلب بنجاح.' 
				: 'Order status updated successfully.');
	}

	public function markPaid(Request $request, Order $order)
	{
		$user = Auth::user();
		$pharmacy = Pharmacy::find($user->pharmacy_id);

		if (!$pharmacy || $order->pharmacy_id !== $pharmacy->id) {
			return redirect()->route('pharmacies.orders.index')
				->with('error', app()->getLocale() === 'ar' 
					? 'غير مصرح لك بتعديل هذا الطلب.' 
					: 'You are not authorized to update this order.');
		}

		// Store old payment status before updating
		$wasPaid = $order->payed;

		// Only update and notify if order wasn't already paid
		if (!$wasPaid) {
			// Update the order payment status
			$order->update(['payed' => true]);
			
			// Refresh and load necessary relationships after updating
			$order->refresh();
			$order->load(['request.client', 'pharmacy']);

			// Send notification to client
			try {
				if ($order->request && $order->request->client) {
					$client = $order->request->client;
					$client->notify(new OrderPaidNotification($order));
				}
			} catch (\Exception $e) {
				// Log error but don't fail the request
				Log::error('Failed to send order paid notification: ' . $e->getMessage());
			}
		}

		return redirect()->route('pharmacies.orders.index')
			->with('success', app()->getLocale() === 'ar' 
				? 'تم تحديد الطلب كمدفوع بنجاح.' 
				: 'Order marked as paid successfully.');
	}
}

