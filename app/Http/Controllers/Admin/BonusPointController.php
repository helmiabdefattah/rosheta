<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusPoint;
use Illuminate\Http\Request;

class BonusPointController extends Controller
{
	public function index()
	{
		$points = BonusPoint::with(['client' => function ($q) {
			$q->select('id', 'name', 'email');
		}])->latest()->paginate(20);

		return view('admin.bonus-points.index', compact('points'));
	}
}


