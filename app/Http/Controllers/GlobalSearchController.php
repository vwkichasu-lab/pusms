<?php

namespace App\Http\Controllers;

use App\Services\StudentReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request, StudentReportService $reports): Response
    {
        abort_unless($request->user()?->hasRole('Super Administrator'), 403);

        return response()->view('reports.global-search', [
            'rows' => filled($request->query('q')) ? $reports->rows($request->query()) : collect(),
            'query' => $request->query('q'),
        ]);
    }
}
