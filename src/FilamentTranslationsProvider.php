<?php

namespace io3x1\FilamentTranslations;

use Filament\PluginServiceProvider;
use Filament\Navigation\NavigationItem;
use Filament\Facades\Filament;
use Spatie\LaravelPackageTools\Package;

class FilamentTranslationsProvider extends PluginServiceProvider
{
    public static string $name = 'filament-translations';

    public function configurePackage(Package $package): void
    {
        $package->name('filament-translations');
    }

    public function boot(): void
    {
        Filament::registerNavigationItems([
            NavigationItem::make()
                ->group('Translations')
                ->icon('heroicon-o-translate')
                ->label('Change Language')
                ->sort(10)
                ->url(url('admin/change')),
        ]);

        Filament::registerNavigationGroups([
            'Translations'
        ]);

        $this->publishes([
            __DIR__ . '/../database/migrations' => base_path('database/migrations'),
            __DIR__ . '/../publish' => app_path(),
            __DIR__ . '/../config' => config_path(),
            __DIR__ . '/../resources' => resource_path(),
            __DIR__ . '/../stubs/FilamentTranslationsProvider.php' => app_path('Providers/FilamentTranslationsProvider.php'),
        ], 'filament-translations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}
