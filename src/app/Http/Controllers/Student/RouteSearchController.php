<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\EkispertClient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RouteSearchController extends Controller
{
    public function index(Request $request, EkispertClient $client)
    {
        // 初回はデフォルト表示（検索未実行）
        $from = $request->string('from')->toString();
        $to   = $request->string('to')->toString();

        $results = [];
        if ($from && $to) {
            $when = $request->filled('when')
                ? Carbon::parse($request->input('when'))
                : Carbon::now()->setTime(8, 0);

            $results = $client->search($from, $to, $when, 3);
            // 念のためフォールバックURL（全コース共通）
            $fallbackUrl = $client->resourceUrl($from, $to, $when);
            foreach ($results as &$r) {
                if (empty($r['resource_url'])) $r['resource_url'] = $fallbackUrl;
            }
        }

        return view('student.routes.index', compact('results'));
    }
}