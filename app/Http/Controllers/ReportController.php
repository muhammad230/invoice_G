<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /reports — filters + summary cards + chart canvases.
     * The chart data itself is fetched from the JSON endpoints below, so
     * changing a filter only reloads the page and the charts re-fetch.
     */
    public function index(Request $request, ReportService $service): View
    {
        $user     = $request->user();
        $currency = $service->resolveCurrency($user, $request->query('currency'));
        $range    = $service->resolveRange(
            $request->query('range'),
            $request->query('from'),
            $request->query('to')
        );

        $cards = $service->cards($user, $currency['code'], $range);

        // Query string shared by the currency selector, the CSV link and the
        // chart fetches. Custom ranges carry their explicit from/to dates.
        $query = ['currency' => $currency['code'], 'range' => $range['range']];
        if ($range['range'] === 'custom') {
            $query['from'] = $range['from_text'];
            $query['to']   = $range['to_text'];
        }

        return view('reports.index', [
            'currency' => $currency,
            'range'    => $range,
            'cards'    => $cards,
            'ranges'   => $service->ranges(),
            'query'    => $query,
        ]);
    }

    /**
     * GET /reports/chart/revenue-by-month — bar chart JSON.
     */
    public function revenueByMonth(Request $request, ReportService $service): JsonResponse
    {
        $user     = $request->user();
        $currency = $service->resolveCurrency($user, $request->query('currency'));
        $range    = $service->resolveRange(
            $request->query('range'),
            $request->query('from'),
            $request->query('to')
        );

        return response()->json(array_merge(
            $service->revenueByMonth($user, $currency['code'], $range),
            ['currency' => $currency['code'], 'symbol' => $currency['symbol']]
        ));
    }

    /**
     * GET /reports/chart/paid-unpaid — doughnut JSON (all-time per currency,
     * so paid + pending + overdue always equals the invoice count).
     */
    public function paidUnpaid(Request $request, ReportService $service): JsonResponse
    {
        $user     = $request->user();
        $currency = $service->resolveCurrency($user, $request->query('currency'));

        return response()->json(array_merge(
            $service->paidUnpaid($user, $currency['code']),
            ['currency' => $currency['code'], 'symbol' => $currency['symbol']]
        ));
    }

    /**
     * GET /reports/chart/top-clients — top 5 paying clients, horizontal bar.
     */
    public function topClients(Request $request, ReportService $service): JsonResponse
    {
        $user     = $request->user();
        $currency = $service->resolveCurrency($user, $request->query('currency'));
        $range    = $service->resolveRange(
            $request->query('range'),
            $request->query('from'),
            $request->query('to')
        );

        return response()->json(array_merge(
            $service->topClients($user, $currency['code'], $range),
            ['currency' => $currency['code'], 'symbol' => $currency['symbol']]
        ));
    }

    /**
     * GET /reports/chart/revenue-by-service — top 8 services, bar JSON.
     */
    public function revenueByService(Request $request, ReportService $service): JsonResponse
    {
        $user     = $request->user();
        $currency = $service->resolveCurrency($user, $request->query('currency'));
        $range    = $service->resolveRange(
            $request->query('range'),
            $request->query('from'),
            $request->query('to')
        );

        return response()->json(array_merge(
            $service->revenueByService($user, $currency['code'], $range),
            ['currency' => $currency['code'], 'symbol' => $currency['symbol']]
        ));
    }

    /**
     * GET /reports/export — stream the filtered CSV (one currency, one range).
     */
    public function export(Request $request, ReportService $service): StreamedResponse
    {
        $user     = $request->user();
        $currency = $service->resolveCurrency($user, $request->query('currency'));
        $range    = $service->resolveRange(
            $request->query('range'),
            $request->query('from'),
            $request->query('to')
        );

        $csv = $service->csvFor($user, $currency['code'], $range);

        $filename = sprintf(
            'reports-%s-%s_to_%s.csv',
            $currency['code'],
            $range['from']->format('Ymd'),
            $range['to']->format('Ymd')
        );

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}