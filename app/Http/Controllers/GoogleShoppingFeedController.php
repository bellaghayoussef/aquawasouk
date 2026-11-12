<?php

namespace App\Http\Controllers;

use App\Services\GoogleShoppingFeedService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GoogleShoppingFeedController extends Controller
{
    protected GoogleShoppingFeedService $feedService;

    public function __construct(GoogleShoppingFeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    /**
     * Serve Google Merchant Center XML product catalog feed.
     * Accessible publicly at: GET /google-shopping.xml
     */
    public function index(Request $request): Response
    {
        // Support optional forced refresh (?refresh=1, ?clear_cache=1) or automatically in local environment
        $forceRefresh = $request->boolean('refresh') || $request->boolean('clear_cache') || app()->isLocal();

        $xml = $this->feedService->getFeedXml($forceRefresh);

        $cacheControl = app()->isLocal()
            ? 'no-cache, no-store, must-revalidate'
            : 'public, max-age=3600, s-maxage=3600, stale-while-revalidate=7200';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => $cacheControl,
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }
}

