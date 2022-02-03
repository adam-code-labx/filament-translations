<?php

namespace io3x1\FilamentTranslations\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use io3x1\FilamentTranslations\Services\Scan;
use io3x1\FilamentTranslations\Models\Translation;
use Illuminate\Support\Facades\DB;

class SaveScan
{
    private $paths;

    public function __construct()
    {
        $this->paths = config('filament-translations.paths');
    }

    public function save()
    {
        $scanner = app(Scan::class);
        collect($this->paths)->each(function ($path) use ($scanner) {
            $scanner->addScannedPath($path);
        });

        [$trans, $__] = $scanner->getAllViewFilesWithTranslations();

        /** @var Collection $trans */
        /** @var Collection $__ */

        DB::transaction(function () use ($trans, $__) {
            Translation::query()
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => Carbon::now()
                ]);

            $trans->each(function ($trans) {
                [$group, $key] = explode('.', $trans, 2);
                $namespaceAndGroup = explode('::', $group, 2);
                if (count($namespaceAndGroup) === 1) {
                    $namespace = '*';
                    $group = $namespaceAndGroup[0];
                } else {
                    [$namespace, $group] = $namespaceAndGroup;
                }
                $this->createOrUpdate($namespace, $group, $key);
            });

            $__->each(function ($default) {
                $this->createOrUpdate('*', '*', $default);
            });
        });
    }

    /**
     * @param $namespace
     * @param $group
     * @param $key
     */
    protected function createOrUpdate($namespace, $group, $key): void
    {
        /** @var Translation $translation */
        $translation = Translation::withTrashed()
            ->where('namespace', $namespace)
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        $defaultLocale = config('app.locale');

        if ($translation) {
            if (!$this->isCurrentTransForTranslationArray($translation, $defaultLocale)) {
                $translation->restore();
            }
        } else {


            $translation = Translation::make([
                'namespace' => $namespace,
                'group' => $group,
                'key' => $key,
            ]);

            $translation->text = $this->getTranslationFromJsonArray($translation);

            if (!$this->isCurrentTransForTranslationArray($translation, $defaultLocale)) {
                $translation->save();
            }
        }
    }

    /**
     * @param Translation $translation
     * @param $locale
     * @return array
     */
    private function getTranslationFromJsonArray(Translation $translation): array
    {
        $locales = config('filament-translations.locals');

        return collect($locales)->flatMap(
            fn($locale) => [$locale => $this->getJsonTranslationByLocale($translation, $locale)]
        )->filter()->toArray();
    }

    /**
     * @param Translation $translation
     * @param $locale
     * @return bool
     */
    private function isCurrentTransForTranslationArray(Translation $translation, $locale): bool
    {
        if ($translation->group === '*') {
            return is_array(__($translation->key, [], $locale));
        }

        if (!$translation->namespace && $translation->namespace === '*') {
            return is_array(trans($translation->group . '.' . $translation->key, [], $locale));
        }

        return is_array(trans($translation->namespace . '::' . $translation->group . '.' . $translation->key, [], $locale));
    }

    /**
     * @param Translation $translation
     * @param $locale
     * @return string
     */
    private function getJsonTranslationByLocale(Translation $translation, string $locale): string
    {
        if (!$translation->namespace || $translation->namespace === '*') {
            $key = $translation->group . '.' . $translation->key;
            $value = trans($translation->group . '.' . $translation->key, [], $locale);
            return $key !== $value ? $value : '';
        }

        return '';
    }
}
