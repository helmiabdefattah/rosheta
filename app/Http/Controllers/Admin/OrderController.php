<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.orders.index');
    }

    public function show(Order $order)
    {
        $order->load(['request.client', 'pharmacy', 'offer', 'lines']);
        return view('admin.orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الطلب بنجاح' : 'Order deleted successfully');
    }

    public function data()
    {
        $orders = Order::with(['request.client', 'pharmacy'])->select('orders.*');

        return DataTables::of($orders)
            ->addColumn('request_id', function ($order) {
                return $order->request->id ?? '-';
            })
            ->addColumn('client_name', function ($order) {
                return $order->request->client->name ?? '-';
            })
            ->addColumn('pharmacy_name', function ($order) {
                return $order->pharmacy->name ?? '-';
            })
            ->addColumn('status_badge', function ($order) {
                $colors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'delivered' => 'bg-green-100 text-green-800',
                    'delivering' => 'bg-blue-100 text-blue-800',
                ];
                $color = $colors[$order->status] ?? 'bg-gray-100 text-gray-800';
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' . ucfirst($order->status) . '</span>';
            })
            ->addColumn('total_price_formatted', function ($order) {
                return $order->total_price ? 'EGP ' . number_format($order->total_price, 2) : '-';
            })
            ->addColumn('payed_badge', function ($order) {
                return $order->payed 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . (app()->getLocale() === 'ar' ? 'مدفوع' : 'Paid') . '</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' . (app()->getLocale() === 'ar' ? 'غير مدفوع' : 'Unpaid') . '</span>';
            })
            ->addColumn('actions', function ($order) {
                return view('admin.orders.actions', compact('order'))->render();
            })
            ->rawColumns(['status_badge', 'payed_badge', 'actions'])
            ->make(true);
    }
}

