<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Suggestion;

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

        // Add static pages
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/community-ideas')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));

        $sitemap->add(Url::create('/login')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.5));

        $sitemap->add(Url::create('/register')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.5));

        // Add API documentation pages
        // Get the view data from getApiRoutes which contains API routes
        $viewData = app(ServiceInfoController::class)->getApiRoutes();

        // Check if the view data contains API routes
        if (isset($viewData->getData()['apis'])) {
            $apis = $viewData->getData()['apis'];

            foreach ($apis as $api) {
                if (isset($api['key'])) {
                    $sitemap->add(
                        Url::create("/docs/{$api['key']}")
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                }
            }
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
