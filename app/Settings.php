<?php

namespace App;

use Illuminate\Support\Facades\DB;

class Settings
{
    public const string = 0;
    public const int = 1;
    public const bool = 2;

    /** @var string[] Settings store. */
    protected static array $settings;

    /** @var bool Loaded state. */
    protected static bool $loaded = false;

    /**
     * Get value from settings.
     *
     * @param string $key
     * @param mixed $default
     * @param int $type
     *
     * @return  mixed
     */
    public static function get(string $key, $default = null, int $type = Settings::string)
    {
        if (!self::$loaded) {
            self::load();
        }

        return (self::$settings && array_key_exists($key, self::$settings)) ? self::castTo(self::$settings[$key], $type) : $default;
    }

    /**
     * Set value to settings.
     *
     * @param string $key
     * @param mixed $value
     * @param int $type
     *
     * @return  void
     */
    public static function set(string $key, $value, int $type = Settings::string): void
    {
        if (!self::$loaded) {
            self::load();
        }

        self::$settings[$key] = self::castFrom($value, $type);
    }

    /**
     * Load settings.
     *
     * @return  void
     */
    public static function load(): void
    {
        $settings = DB::table('settings')->get();

        self::$settings = [];

        foreach ($settings as $setting) {
            self::$settings[$setting->key] = $setting->value;
        }

        self::$loaded = true;
    }

    /**
     * Save settings.
     *
     * @return  void
     */
    public static function save(): void
    {
        if (self::$loaded === false) {
            throw new \RuntimeException('Settings must be loaded before saving.');
        }
        $values = [];
        foreach (self::$settings as $key => $value) {
            $values[] = ['key' => $key, 'value' => $value];
        }
        DB::table('settings')->truncate();
        DB::table('settings')->insert($values);
    }

    /**
     * Cast value to type.
     *
     * @param string|null $value
     * @param int $type
     *
     * @return  bool|int|string
     */
    protected static function castTo(?string $value, int $type)
    {
        switch ($type) {
            case self::int:
                $value = (int)$value;
                break;
            case self::bool:
                $value = (bool)$value;
                break;
            case self::string:
            default:
        }

        return $value;
    }

    /**
     * Cast value from type.
     *
     * @param bool|int|string|null $value
     * @param int $type
     *
     * @return  string|null
     */
    protected static function castFrom($value, int $type): ?string
    {
        switch ($type) {
            case self::int:
            case self::bool:
                $value = (string)$value;
                break;
            case self::string:
            default:
        }

        return $value;
    }
}
