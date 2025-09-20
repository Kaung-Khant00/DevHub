<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminReportController extends Controller
{
    public function getReports()
    {
        $page = request()->query('page', 1);
        $per_page = request()->query('per_page', 10);
        $type = request()->query('type');
        $reports = Report::
            when($type, function ($query) use ($type) {
                return $query->where('reportable_type', $type);
            })
            ->with('reportable')
            ->with('reporter')
            ->paginate($per_page, ['*'], 'page', $page);
        return response()->json([
            'reports' => $reports,
        ]);
    }
}
