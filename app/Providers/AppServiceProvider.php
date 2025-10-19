<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add logo to all invoice views
        \Illuminate\Support\Facades\View::composer(['pdf.invoice'], function ($view) {
            $logoPath   = public_path('images/logo-osi.png');
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            $view->with('logo_base64', $logoBase64);

            $logoBcaPath   = public_path('images/logo-bca.png');
            $logoBcaBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoBcaPath));
            $view->with('logo_bca_base64', $logoBcaBase64);

            $logoInstagramPath   = public_path('images/instagram.png');
            $logoInstagramBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoInstagramPath));
            $view->with('logo_instagram_base64', $logoInstagramBase64);

            $logoWhatsAppPath   = public_path('images/whatsapp.png');
            $logoWhatsAppBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoWhatsAppPath));
            $view->with('logo_whatsapp_base64', $logoWhatsAppBase64);

            $logoWebsitePath   = public_path('images/website.png');
            $logoWebsiteBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoWebsitePath));
            $view->with('logo_website_base64', $logoWebsiteBase64);
        });
    }
}
