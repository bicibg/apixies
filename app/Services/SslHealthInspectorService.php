<?php

namespace App\Services;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use UAParser\Parser;
use Illuminate\Support\Carbon;

class SslHealthInspectorService
{
    private Parser         $parser;
    private CrawlerDetect  $crawler;

    public function __construct()
    {
        // UA‑Parser for structured data, CrawlerDetect for bot flag
        $this->parser  = Parser::create();
        $this->crawler = new CrawlerDetect();
    }

    public function inspect(string $ua): array
    {
        $result = $this->parser->parse($ua);

        $device = [
            'family' => $result->device->family ?? null,
            'model'  => $result->device->model  ?? null,
            'brand'  => $result->device->brand  ?? null,
        ];

        $os = [
            'family' => $result->os->family   ?? null,
            'major'  => $result->os->major    ?? null,
            'minor'  => $result->os->minor    ?? null,
            'patch'  => $result->os->patch    ?? null,
        ];

        $browser = [
            'family' => $result->ua->family   ?? null,
            'major'  => $result->ua->major    ?? null,
            'minor'  => $result->ua->minor    ?? null,
            'patch'  => $result->ua->patch    ?? null,
        ];

        return [
            'user_agent' => $ua,
            'is_bot'     => $this->crawler->isCrawler($ua),
            'device'     => $device,
            'os'         => $os,
            'browser'    => $browser,
            'scanned_at' => Carbon::now()->toIso8601String(),
        ];
    }
}
