<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Report;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;

final class ReportController
{
    public function index(Request $request): Responsable
    {
        $request->validate([
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

    }
}
