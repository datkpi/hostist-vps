<?php

namespace App\Repositories;

use App\Models\Config;
use App\Repositories\Support\AbstractRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ConfigRepository extends AbstractRepository
{
    /**
     * Specify Model class name
     */
    public function model()
    {
        return Config::class;
    }

    /**
     * Cache key for config
     */
    const CACHE_KEY = 'site_config';

    /**
     * Get current config
     */
    public function getCurrent()
    {
        return Cache::remember(self::CACHE_KEY, 60 * 24, function () {
            return $this->model->first();
        });
    }

    /**
     * Get config value by key
     */
    public function get($key, $default = null)
    {
        $config = $this->getCurrent();
        return $config ? $config->{$key} : $default;
    }

    /**
     * Update config
     */
    public function updateConfig(array $data)
    {
        try {
            // Log input data
            Log::info('Validating config data:', $data);

            // Validate data
            $data = $this->validateAndClean($data, [
                'site_name' => 'nullable|string|max:255',
                // ... other validation rules
            ]);

            Log::info('Data after validation:', $data);

            $config = $this->getCurrent();

            if (!$config) {
                Log::info('Creating new config');
                $result = $this->create($data);
            } else {
                Log::info('Updating existing config:', ['id' => $config->id]);
                $result = $this->update($data, $config->id);
            }

            // Clear cache after update
            Cache::forget(self::CACHE_KEY);

            Log::info('Config updated successfully:', [
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Config update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update single config
     */
    public function updateSingle($key, $value)
    {
        $config = $this->getCurrent();
        if (!$config) {
            return $this->create([$key => $value]);
        }

        $result = $this->update([$key => $value], $config->id);
        Cache::forget(self::CACHE_KEY);

        return $result;
    }

    /**
     * Toggle boolean config value
     * @param string $key Key cần toggle
     * @return Config|null
     */
    public function toggleConfig($key)
    {
        $config = $this->getCurrent();
        if (!$config) {
            return null;
        }

        return $this->updateSingle($key, !$config->{$key});
    }

    /**
     * Override toggle method từ AbstractRepository
     * @param int $id
     * @param string $field
     * @return bool
     */
    public function toggle($id, $field = 'status')
    {
        return parent::toggle($id, $field);
    }
}
