<?php

namespace Mk4U\Http;


/**
 * Uri class
 */
class Uri
{
    protected string $scheme = '';
    protected string $userInfo = '';
    protected string $host = '';
    protected ?int $port = NULL;
    protected string $path = '';
    protected string $query = '';
    protected string $fragment = '';

    private const  DEFAULT_PORT = [
        'http'  => 80,
        'https' => 443,
    ];

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            if (empty($parse = parse_url($uri))) {
                //Unable to parse URI
                throw new \InvalidArgumentException("Unable to parse URI");
            }
            $this->setParts($parse);
        }
    }

    /** 
     * Establece el esquema de la url
     */
    public function setScheme(string $scheme = ''): Uri
    {
        if (strpos($scheme, '?')) {
            $this->scheme = str_replace('://', '', strtolower($scheme));
        } else {
            $this->scheme = $scheme;
        }

        return clone $this;
    }

    /**
     * Devuelve una instancia con la información del usuario especificada.
     *
     * Este método DEBE conservar el estado de la instancia actual y devolver
     * una instancia que contiene la información del usuario especificada.
     *
     * La contraseña es opcional, pero la información del usuario DEBE incluir el
     * usuario; una cadena vacía para el usuario equivale a eliminar al usuario
     * información.
     *
     * @param string $user El nombre de usuario que se utilizará para obtener autoridad.
     * @param null|string $contraseña La contraseña asociada con $usuario.
     * @return static Una nueva instancia con la información de usuario especificada.
     */
    public function setUserInfo(string $user, ?string $password = NULL): static
    {
        if (isset($user)) $this->userInfo = $user;
        if (isset($password)) $this->userInfo .= ':' . $password;

        return clone $this;
    }

    /** 
     * Establece el host de la url
     */
    public function setHost(string $host = ''): Uri
    {
        $this->host = strtolower($host);
        return clone $this;
    }

    /** 
     * Establece puerto
     */
    public function setPort(?int $port = NULL): Uri
    {
        if (isset($port) && $port <= 0 || $port > 65535) {
            throw new \InvalidArgumentException(sprintf('Invalid port: %d. It must be between 0 and 65535', $port));
        }
        $this->port = $port;

        return clone $this;
    }

    /** 
     * Establece la ruta de la url
     * 
     * Si la ruta contiene parametros de consulta los envia a setQuery()
     */
    public function setPath(string $path = '/'): Uri
    {
        if (strpos($path, '?')) $this->path = substr($path, 0, strpos($path, '?'));
        else $this->path = $path;

        return clone $this;
    }

    /** 
     * Establece las consultas de la url
     */
    public function setQuery(string $query = ''): Uri
    {
        $this->query = explode('#', ltrim(
            substr($query, strpos($query, '?')),
            '?'
        ))[0];
        return clone $this;
    }

    /** 
     * Establece el fragmento de URI especificado
     */
    public function setFragment(string $fragment = ''): Uri
    {
        $this->fragment = ltrim(substr($fragment, strpos($fragment, '#')), '#');
        return clone $this;
    }

    /** 
     * Recuperar el componente de esquema de la URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-3.1 
     */
    public function getScheme(): string
    {
        return $this->normalize($this->scheme);
    }

    /** 
     * Recuperar el componente host del URI.
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2 
     */
    public function getHost(): string
    {
        return $this->normalize($this->host);
    }


    /** 
     * Recuperar el componente de puerto de la URI.
     */
    public  function  getPort(): ?int
    {
        if ($this->getScheme() == '' && isset($this->port)) {
            return null;
        }
        foreach (self::DEFAULT_PORT as $key => $value) {
            if ($key === $this->getScheme() && $value === $this->port) {
                return null;
            }
        }
        return $this->port;
    }

    /** 
     * Recuperar el componente de ruta del URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2 
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     */
    public  function  getPath(): string
    {
        return $this->path;
    }

    /** 
     * Recuperar la cadena de consulta de la URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2 
     * @see https://tools.ietf.org/html/rfc3986#section-3.4 
     */
    public  function  getQuery(bool $array = false): array|string
    {
        if ($array) {
            parse_str($this->query, $arr);
            return $arr;
        }
        return $this->query;
    }

    /** 
     * Recuperar el componente de fragmento de la URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2 
     * @see https://tools.ietf.org/html/rfc3986#section-3.5 
     */
    public  function  getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * Recuperar el componente de autoridad del URI.
     *
     * Si no hay información de autoridad presente, este método DEBE devolver un valor vacío cadena.
     *
     * La sintaxis de autoridad del URI es:
     *
     * <pre>
     * [información-usuario@]host[:puerto]
     * </pre>
     *
     * Si el componente del puerto no está configurado o es el puerto estándar para el actual
     * esquema, NO DEBE incluirse.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string La autoridad URI, en formato "[user-info@]host[:port]".
     */
    public function getAuthority(): string
    {
        $auth = '';
        $user = '';

        if ($this->userInfo !== '') {
            $user = "{$this->userInfo}@";
        }

        if ($this->scheme != '') {
            $auth .= "{$this->scheme}://";
        }

        $auth .= "$user{$this->host}";
        if ($this->port != null) {
            $auth .= ':' . $this->port;
        }
        return $auth;
    }

    /**
     * Recuperar el componente de información del usuario del URI.
     *
     * Si no hay información del usuario presente, este método DEBE devolver un valor vacío
     * cadena.
     *
     * Si un usuario está presente en la URI, esto devolverá ese valor;
     * Además, si la contraseña también está presente, se agregará al
     * valor de usuario, con dos puntos (":") separando los valores.
     *
     * El carácter "@" final no forma parte de la información del usuario y NO DEBE
     * agregarce.
     *
     * @return string La información del usuario URI, en formato "nombre de usuario[:contraseña]".
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /** 
     * Devuelve la representación de la URI como texto. 
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-4.1 
     */
    public function __toString(): string
    {
        $uri = $this->getAuthority() . $this->getPath();
        if (!empty($this->getQuery())) $uri .= '?' . $this->getQuery();
        if (!empty($this->getFragment())) $uri .= '#' . $this->getFragment();

        return $uri;
    }

    /**
     * Normalizar a minusculas cadena de caracteres
     */
    private function normalize(string $var): string
    {
        return strtolower($var);
    }

    /**
     * Establece el valor de cada propiedad de URI
     */
    private function setParts(array $parts): void
    {
        $this->setScheme($parts['scheme'] ?? '');
        $this->setUserInfo($parts['user'] ?? '', $parts['pass'] ?? null);
        $this->setHost($parts['host'] ?? '');
        $this->setPort($parts['port'] ?? null);
        $this->setPath($parts['path'] ?? '');
        $this->setQuery($parts['query'] ?? '');
        $this->setFragment($parts['fragment'] ?? '');
    }

    /** 
     * Devuelve la representación de la URI como array. 
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-4.1 
     */
    public function __debugInfo(): array
    {
        return [
            'schema' => $this->getScheme(),
            'user-info' => $this->userInfo,
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'path' => $this->getPath(),
            'query' => $this->getQuery(),
            'fragment' => $this->getFragment(),
        ];
    }
}
