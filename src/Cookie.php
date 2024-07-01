<?php

namespace Mk4U\Http;

use RuntimeException;

/**
 * Cookie class
 */
class Cookie
{
    /**
     * Agrega la cookie antes de enviarla al navegador
     */
    public static function set(
        string $name,
        mixed $value,
        int $expires = 0,
        string $path = '/',
        string $domain = null,
        bool $secure = false,
        bool $httponly = false
    ): bool {
        if ($expires != 0) {
            $expires = time() + $expires;
        }
        return setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }

    /**
     * Obtiene valores de $_COOKIE
     */
    public static function get(?string $name = null, mixed $default = null): mixed
    {
        if (is_null($name)) {
            return $_COOKIE;
        }

        if (isset($default)) {
            return $default;
        } else {
            if (self::has($name) == false) {
                throw new RuntimeException(sprintf("The cookie %s does not exist", $name));
            }

            return $_COOKIE[$name];
        }
    }

    /**
     * Verifica que exista la cookie
     */
    public static function has(string $name): bool
    {
        return key_exists($name, $_COOKIE);
    }

    /**
     * Elimina una cookie
     */
    public  static function remove(string $name): void
    {
        self::set($name, '', -1);
    }
}
