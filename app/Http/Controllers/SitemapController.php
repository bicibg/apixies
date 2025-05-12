<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /**
     * Generate the sitemap for the application.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        // Only allow in local environment or with a token in production
        if (!App::environment('local') &&
            (!$request->has('token') || $request->token !== config('app.sitemap_secret'))) {
            return response('Unauthorized', 403);
        }

        $sitemap = $this->buildSitemap();
        $sitemap->writeToFile(public_path('sitemap.xml'));

        return response('Sitemap generated successfully at ' . now());
    }

    /**
     * Build the sitemap structure based on application routes and content.
     *
     * @return \Spatie\Sitemap\Sitemap
     */
    protected function buildSitemap()
    {
        $sitemap = Sitemap::create();

        // Add core pages from your routes
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/community-ideas')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.9));

        $sitemap->add(Url::create('/login')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.5));

        $sitemap->add(Url::create('/register')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.5));

        // Add API docs index
        $sitemap->add(Url::create('/docs')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.8));

        // Add your API documentation routes
        // You can hardcode a few key API routes here if you know them
        $apiRoutes = [
            // Example: Add your most important API documentation pages
            // '/docs/auth',
            // '/docs/api-keys',
            // Add more as needed
        ];

        foreach ($apiRoutes as $route) {
            $sitemap->add(
                Url::create($route)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7)
            );
        }

        return $sitemap;
    }

    /**
     * Command line sitemap generation.
     *
     * @return void
     */
    public function generateFromConsole()
    {
        $sitemap = $this->buildSitemap();
        $sitemap->writeToFile(public_path('sitemap.xml'));

        echo "Sitemap generated successfully at " . now() . PHP_EOL;
    }
}
