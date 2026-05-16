<?php

namespace YandexFeed\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use YandexFeed\Models\YandexFeed;
use YandexFeed\Services\YmlGeneratorService;

class YandexFeedController extends Controller
{

    public function show(string $fileName): Response
    {
        $feed = YandexFeed::where('file_name', $fileName)
            ->where('is_active', true)
            ->firstOrFail();

        $filePath = $feed->getFeedPath();

        if (file_exists($filePath)) {
            $xmlContent = file_get_contents($filePath);
        } else {
            abort(404);
        }

        return response($xmlContent, 200)
            ->header('Content-Type', 'application/xml; charset=' . $feed->encoding);
    }


}

