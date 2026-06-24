<?php

namespace App\Events;

use App\Libraries\MY_Migration;
use App\Models\Appconfig;
use CodeIgniter\Session\Session;
use Config\OSPOS;
use Config\Services;

/**
 * @property Appconfig appconfig;
 * @property MY_Migration migration;
 * @property Session session;
 * @property mixed $config
 * @property mixed $migration_config
 */
class Load_config
{
    public Session $session;

    public function load_config(): void
    {
        $migration_config = config('Migrations');
        $migration        = new MY_Migration($migration_config);

        $this->session = session();

        $config = config(OSPOS::class);

        if (! $migration->is_latest()) {
            $this->session->destroy();
        }

        $this->setDefaultLanguage($config);

        $language = Services::language();
        $language->setLocale(current_language_code());

        date_default_timezone_set($config->settings['timezone'] ?? ini_get('date.timezone'));

        bcscale(max(2, totals_decimals() + tax_decimals()));
    }

    private function setDefaultLanguage(OSPOS $config): void
    {
        $languageCode = $config->settings['language_code'] ?? null;

        if (empty($config->settings) || $languageCode === null) {
            $config->settings['language']      = 'english';
            $config->settings['language_code'] = 'en';

            return;
        }

        if (! $this->languageExists($languageCode)) {
            $config->settings['language']      = 'english';
            $config->settings['language_code'] = 'en';
        }
    }

    private function languageExists(string $languageCode): bool
    {
        return file_exists(APPPATH . 'Language/' . $languageCode);
    }
}
