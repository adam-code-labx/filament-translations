<?php

namespace App\Providers;

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
                ->label('Change Language Override')
                ->sort(10)
                ->url(url('admin/change')),
        ]);

        Filament::registerNavigationGroups([
            'Translations'
        ]);
    }
}
