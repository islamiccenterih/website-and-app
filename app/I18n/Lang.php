<?php

declare(strict_types=1);

namespace App\I18n;

final class Lang
{
    public const COOKIE = 'ic_lang';

    /** @var array<string, array{name:string,native:string,dir:string,html:string}> */
    public const LOCALES = [
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr', 'html' => 'en'],
        'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'dir' => 'ltr', 'html' => 'hi'],
        'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'dir' => 'rtl', 'html' => 'ur'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl', 'html' => 'ar'],
    ];

    private static string $code = 'en';

    /** @var array<string, string>|null */
    private static ?array $map = null;

    public static function boot(): void
    {
        $code = 'en';
        $fromCookie = $_COOKIE[self::COOKIE] ?? '';
        if (is_string($fromCookie) && isset(self::LOCALES[$fromCookie])) {
            $code = $fromCookie;
        }
        $fromSession = $_SESSION['lang'] ?? '';
        if (is_string($fromSession) && isset(self::LOCALES[$fromSession])) {
            $code = $fromSession;
        }
        self::$code = $code;
        self::$map = null;
    }

    public static function set(string $code): void
    {
        if (!isset(self::LOCALES[$code])) {
            $code = 'en';
        }
        self::$code = $code;
        self::$map = null;
        $_SESSION['lang'] = $code;
        $secure = request_is_https();
        setcookie(self::COOKIE, $code, [
            'expires' => time() + 86400 * 365,
            'path' => '/',
            'secure' => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE] = $code;
    }

    public static function code(): string
    {
        return self::$code;
    }

    public static function dir(): string
    {
        return self::LOCALES[self::$code]['dir'] ?? 'ltr';
    }

    public static function html(): string
    {
        return self::LOCALES[self::$code]['html'] ?? 'en';
    }

    public static function isRtl(): bool
    {
        return self::dir() === 'rtl';
    }

    public static function text(string $text): string
    {
        $trim = trim($text);
        if ($trim === '' || self::$code === 'en') {
            return $text;
        }
        $map = self::map();
        return $map[$trim] ?? $text;
    }

    /**
     * @return array<string, string>
     */
    private static function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }
        $all = require __DIR__ . '/dictionary.php';
        $lang = is_array($all[self::$code] ?? null) ? $all[self::$code] : [];
        self::$map = $lang;
        return self::$map;
    }
}
