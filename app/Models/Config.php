<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // Casts cho JSON
        'months_name' => 'array',
        'page_of_text' => 'array',

        // Casts cho boolean
        'adsense_non_personalized' => 'boolean',
        'enable_rss' => 'boolean',
        'enable_adsense' => 'boolean',
        'enable_twitter' => 'boolean',
        'enable_youtube' => 'boolean',
        'enable_instagram' => 'boolean',
        'enable_pinterest' => 'boolean',
        'enable_linkedin' => 'boolean',
        'enable_disqus' => 'boolean',
        'enable_github' => 'boolean',
        'enable_gravatar' => 'boolean',

        // Casts cho số nguyên
        'related_posts_num' => 'integer',
        'posts_per_page' => 'integer',
    ];

    /**
     * Lấy instance config hiện tại
     * @return Config|null
     */
    public static function current()
    {
        return self::first();
    }

    /**
     * Lấy giá trị config theo key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $config = self::current();
        return $config ? $config->{$key} : $default;
    }

    /**
     * Cập nhật một hoặc nhiều config
     * @param array $data
     * @return bool
     */
    public static function updateConfig(array $data)
    {
        $config = self::current();
        if (!$config) {
            $config = self::create($data);
            return true;
        }
        return $config->update($data);
    }

    /**
     * Lấy full URL website
     * @return string
     */
    public function getFullUrlAttribute()
    {
        return rtrim($this->url ?? config('app.url'), '/');
    }

    /**
     * Lấy domain từ URL
     * @return string|null
     */
    public function getDomainAttribute()
    {
        if (!$this->url) return null;
        return parse_url($this->url, PHP_URL_HOST);
    }

    /**
     * Convert hex color sang rgba
     * @param float $opacity
     * @return string
     */
    public function getThemeColorRgbaAttribute($opacity = 1)
    {
        $hex = ltrim($this->theme_color ?? '#002ce6', '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba($r, $g, $b, $opacity)";
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Đảm bảo chỉ có một config record
        static::creating(function ($config) {
            if (static::count() > 0) {
                return false;
            }
        });
    }
}
