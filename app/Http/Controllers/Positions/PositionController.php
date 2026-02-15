<?php

namespace App\Http\Controllers\Positions;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('department')
            ->withCount('employees')
            ->orderBy('name')
            ->paginate(20);

        return view('positions.index', compact('positions'));
    }
}
