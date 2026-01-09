<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientOrderController extends Controller
{
	public function index(Request $request)
	{
		$client = Auth::guard('client')->user();

		$query = Order::whereHas('request', function ($q) use ($client) {
				$q->where('client_id', $client->id);
			})
			->with([
				'request.client',
				'pharmacy',
				'offer',
			])
			->orderByDesc('created_at');

		// Filter by status if provided
		if ($request->filled('status')) {
			$query->where('status', $request->string('status'));
		}

		// Filter by payment status if provided
		if ($request->filled('paid')) {
			$query->where('payed', $request->boolean('paid'));
		}

		// Search by order ID or pharmacy name
		if ($request->filled('search')) {
			$term = $request->string('search');
			$query->where(function ($q) use ($term) {
				$q->where('id', 'like', "%{$term}%")
					->orWhereHas('pharmacy', function ($qq) use ($term) {
						$qq->where('name', 'like', "%{$term}%");
					});
			});
		}

		$orders = $query->paginate(15);

		// Load reviews for the orders
		$offerIds = $orders->pluck('offer_id')->filter()->unique();
		$pharmacyIds = $orders->pluck('pharmacy_id')->filter()->unique();

		$reviews = Review::where('client_id', $client->id)
			->where('reviewable_type', Pharmacy::class)
			->whereIn('reviewable_id', $pharmacyIds)
			->whereIn('offer_id', $offerIds)
			->get()
			->keyBy(function ($review) {
				return $review->offer_id . '_' . $review->reviewable_id;
			});

		// Attach reviews to orders
		foreach ($orders as $order) {
			if ($order->pharmacy_id && $order->offer_id) {
				$key = $order->offer_id . '_' . $order->pharmacy_id;
				$order->review = $reviews->get($key);
			}
		}

		return view('client.orders.index', compact('orders'));
	}

	public function storeReview(Request $request, Order $order)
	{
		$client = Auth::guard('client')->user();

		// Verify order belongs to client
		if (!$order->request || $order->request->client_id !== $client->id) {
			return redirect()->route('client.orders.index')
				->with('error', app()->getLocale() === 'ar' 
					? 'غير مصرح لك بمراجعة هذا الطلب.' 
					: 'You are not authorized to review this order.');
		}

		// Verify order has pharmacy
		if (!$order->pharmacy_id) {
			return redirect()->route('client.orders.index')
				->with('error', app()->getLocale() === 'ar' 
					? 'هذا الطلب لا يحتوي على صيدلية.' 
					: 'This order does not have a pharmacy.');
		}

		$validated = $request->validate([
			'rating' => 'required|integer|min:1|max:5',
			'comment' => 'nullable|string|max:2000',
		]);

		// Check if review already exists
		$existingReview = Review::where('client_id', $client->id)
			->where('reviewable_type', Pharmacy::class)
			->where('reviewable_id', $order->pharmacy_id)
			->where('offer_id', $order->offer_id)
			->first();

		if ($existingReview) {
			// Update existing review
			$existingReview->update([
				'rating' => $validated['rating'],
				'comment' => $validated['comment'] ?? null,
			]);

			return redirect()->route('client.orders.index')
				->with('success', app()->getLocale() === 'ar' 
					? 'تم تحديث التقييم بنجاح.' 
					: 'Review updated successfully.');
		}

		// Create new review
		Review::create([
			'reviewable_type' => Pharmacy::class,
			'reviewable_id' => $order->pharmacy_id,
			'client_id' => $client->id,
			'offer_id' => $order->offer_id,
			'rating' => $validated['rating'],
			'comment' => $validated['comment'] ?? null,
		]);

		return redirect()->route('client.orders.index')
			->with('success', app()->getLocale() === 'ar' 
				? 'شكراً لك على التقييم.' 
				: 'Thank you for your review.');
	}
}

