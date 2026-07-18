<?php
namespace LzBuilder;

defined('ABSPATH') || exit;

if (!function_exists('lz_trailingslashit')) {
    function lz_trailingslashit(string $string): string {
        return trailingslashit($string);
    }
}

if (!function_exists('lz_untrailingslashit')) {
    function lz_untrailingslashit(string $string): string {
        return untrailingslashit($string);
    }
}

if (!function_exists('lz_is_ultimo_active')) {
    function lz_is_ultimo_active(): bool {
        return function_exists('wu_has_product') || class_exists('\\WP_Ultimo\\WP_Ultimo');
    }
}

if (!function_exists('lz_get_ultimo_version')) {
    function lz_get_ultimo_version(): string {
        if (defined('WP_ULTIMO_VERSION')) {
            return WP_ULTIMO_VERSION;
        }
        if (defined('LZ_ULTIMATE_MULTISITE_VERSION')) {
            return LZ_ULTIMATE_MULTISITE_VERSION;
        }
        return '';
    }
}
