<?php

namespace Mk4U\Http;

use RuntimeException;

/**
 * Session class
 */
class Session
{
    /**
     * Inicializa la session
     **/
    public static function start()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return session_start([
                "name" => "_mk4u_",
                "use_only_cookies" => true,
                "cookie_lifetime" => 0,
                "cookie_httponly"=> true,
                "cookie_secure"=>true,
                "use_strict_mode" => true,
                //"delete_old_session"=>true,
                //'read_and_close'  => true,
            ]);
        }
    }

    /**
     * Devuelve el valor de $_SESSION
     */
    public static function get(?string $name = null,mixed $default=null): mixed
    {
        if (is_null($name)) {
            return $_SESSION;
        }

        if (isset($default)) {
            return $default;
        } else {
            if (self::has($name) === false) {
                throw new RuntimeException(sprintf("The session '%s' does not exist", $name));
            }
    
            return $_SESSION[$name];
        }
        
    }

    /**
     * Establece valores para $_SESSION
     * 
     * en caso de existir la session sobreescribe el valor
     */
    public static function set(string $name, mixed $value): void
    {
        $_SESSION[$name]=$value;
    }

    /**
     * Elimina valores para $_SESSION
     */
    public static function remove(string $name): void
    {
        unset($_SESSION[$name]);
    }

    /**
     * Verifica si existe una session dada
     */
    public static function has(string $name): bool
    {
        return key_exists($name, $_SESSION);
    }

    /**
     * Genera un nuevo ID de session
     */
    public static function renewId() : void
    {
        session_regenerate_id();
    }

    /**
     * Destruye la sesion con todos sus datos
     */
    public static function destroy() : void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Devuelve el id de la session
     */
    public static function id() : string
    {
        return session_id();
    }
}
