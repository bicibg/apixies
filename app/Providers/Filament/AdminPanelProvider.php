<?php

namespace App\Providers\Filament;

use App\Filament\Pages\ApiStats;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder as Navigation;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->pages([
                ApiStats::class,
            ])
            /* ── Auth: reuse web guard, gate via is_admin ─────────────── */
            ->authGuard('web')
            ->authMiddleware([
                Authenticate::class,
            ])
            /* ── Branding & auto‑discovery ───────────────────────────── */
            ->colors([
                // Use direct color values here instead of CSS variables
                'primary' => Color::hex('#0A2240'),    // Deep navy blue
                'secondary' => Color::hex('#718096'),  // Muted blue-gray
                'gray' => Color::Slate,               // Neutral slate gray
                'danger' => Color::hex('#9B2C2C'),     // Muted red
                'success' => Color::hex('#2F855A'),    // Forest green
                'warning' => Color::hex('#C05621'),    // Burnt orange
                'info' => Color::hex('#2B6CB0'),       // Medium blue
            ])
            ->brandName('Apixies')
            ->assets([
                // Use resource_path for both CSS files
                Css::make('colors', resource_path('css/colors.css')),
                Css::make('custom', resource_path('css/filament/admin/custom-styles.css'))
            ])
            // Larger logo configuration
            ->brandLogo(asset('logo.png'))
            ->brandLogoHeight('5rem') // Increase logo height

            // You can also set a dark mode logo if needed
            // ->darkModeBrandLogo(asset('dark-logo.png'))

            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources',
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets',
            )

            /* ── Shared middleware stack ─────────────────────────────── */
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ]);
    }
}
