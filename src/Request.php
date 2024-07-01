<?php

namespace Mk4U\Http;

/**
 * Request class
 */
class Request
{
    /** @param array datos de carga de archivos*/
    private array $files;

    /** @param string solicitud de destino de la peticion Http*/
    private string $target;

    /** @param string Metodo HTTP*/
    private string $method;

    /** @param Uri instancia de la clase Mk4u\Http\Uri */
    private Uri $uri;

    /** @param array datos pasados por formulario(POST) */
    private array $form_content_type = ['application/x-www-form-urlencoded', 'multipart/form-data'];

    /** @param mixed $content Contenido de la solicitud HTTP */
    private mixed $content = null;

    /** @param array $output Datos parseados del cuerpo del mensaje*/
    private ?array $output = null;

    use Headers;

    /**
     * Crea un nuevo objeto Request
     */
    public function __construct()
    {
        //metodo
        $this->setMethod(self::server('request_method'));

        //URI
        $this->setUri(
            (new Uri())
                ->setScheme(self::server('request_scheme'))
                ->setHost(self::server('http_host'))
                ->setPort(self::server('server_port'))
                ->setPath(self::server('request_uri'))
                ->setQuery(self::server('query_string'))
        );

        //Headers
        $this->setHeaders(getallheaders());

        //Content
        $this->getContent();
    }

    /**
     * Debuguear solicitud HTTP
     */
    public function __debugInfo(): array
    {
        return [
            "method" => $this->getMethod(),
            "uri" => $this->getUri(),
            "protocol" => $this->getProtocolVersion(),
            "headers" => $this->getHeaders(),
            "content" => $this->content
        ];
    }

    /**
     * Devuelve parametros del $_SERVER.
     */
    public static function server(string $index = ''): array|string
    {
        return empty($index) ? $_SERVER : (empty($_SERVER[strtoupper($index)]) ? '' : $_SERVER[strtoupper($index)]);
    }

    /**
     * Obtener solicitud de destino
     * 
     * @see http://tools.ietf.org/html/rfc7230#section-5.3 (para los diversos
     * formularios de destino de solicitud permitidos en mensajes de solicitud)
     */
    public function getTarget(): string
    {
        $this->target = $this->uri->getPath();
        return isset($this->target) ? $this->target : '/';
    }

    /**
     * Obtener metodo http
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Establecer metodo http
     */
    public function setMethod(string $method): Request
    {
        $this->method = strtoupper($method);
        return clone $this;
    }

    /**
     * Verificar metodo http
     */
    public function hasMethod(string $method): bool
    {
        return (strcasecmp($this->method, $method) == 0);
    }

    /**
     * Obtener Uri
     */
    public function getUri(): Uri
    {
        return $this->uri;
    }

    /**
     * Establecer Uri
     */
    public function setUri(Uri $uri, bool $preserv_host = false): Request
    {
        $this->uri = $uri;

        if (!$preserv_host || !$this->hasHeader('host') || $this->getHeader('host') != '') {
            $this->setHeader('host', $uri->getHost());
        }

        return clone $this;
    }

    /**
     * Obtener cuerpo del mensaje HTTP
     **/
    private function getContent(): void
    {
        //contenido
        if (
            in_array($this->getMethod(), ['PUT', 'DELETE', 'PATCH'])
            ||
            ($this->hasMethod('POST') && $this->isFormData() === false)
        ) {
            $this->content = file_get_contents('php://input');
        }

        //archivos
        if ($this->isFormData() && $_FILES) {
            $this->normalizeFiles($_FILES);
        }
    }

    /**
     * Determina si los valores son pasados a traves de un formulario
     **/
    public function isFormData(): bool
    {
        $content_type = explode(';', $this->getHeader('content-type'))[0];
        return ($this->hasMethod('POST') && in_array($content_type, $this->form_content_type));
    }

    /**
     * Obtener parámetros
     *
     * En caso de no especificar el parametro a devolver este metodo devuelve 
     * todos los valores del $params propiedad. Puede agregarle valores por defecto en caso de 
     * que $params[$name] no este definido.
     **/
    private function params(array $params, ?string $name = null, mixed $default = null): mixed
    {
        if (empty($name)) {
            return $params;
        }

        if (!isset($params[$name])) {
            return $default;
        }

        return $params[$name];
    }

    /**
     * Obtener parámetros en la cadena de consulta de la URI
     *
     * En caso de no especificar el parametro a devolver este metodo devuelve 
     * todos los valores de la superglobal $_GET. Puede agregarle valores a $_GET especificando 
     * el nombre del parametro y el valor.
     * 
     * Tenga en cuenta que funciona para todas las solicitudes con una cadena de consulta.
     **/
    public function queryData(?string $name = null, mixed $default = null): mixed
    {
        return $this->params($_GET, $name, $default);
    }

    /**
     * Recuperar los parámetros proporcionados en el cuerpo de la solicitud.
     *
     * Si el tipo de contenido de la solicitud es application/x-www-form-urlencoded
     * o multipart/form-data, y el método de solicitud es POST, este método DEBE
     * devolver el contenido de $_POST.
     *
     * De lo contrario, este método puede devolver cualquier resultado de deserializar
     * el contenido del cuerpo de la solicitud; como el análisis devuelve contenido estructurado, el
     * los tipos potenciales DEBEN ser matrices u objetos solamente. Un valor nulo indica
     * la ausencia de contenido corporal.
     **/
    public function inputData(?string $name = null, mixed $default = null): mixed
    {
        if ($this->isFormData()) {
            return $this->params($_POST, $name, $default);
        }

        if (is_null($this->output)) {
            parse_str($this->content, $this->output);
        }
        return $this->params($this->output, $name, $default);
    }

    /**
     * Devuelve JSON decodificado
     **/
    public function jsonData(bool $assoc = true): array|object|null
    {
        if ($this->getHeader('content-type') == 'application/json') {
            return json_decode($this->content, $assoc, flags: JSON_THROW_ON_ERROR);
        }
        return null;
    }

    /**
     * Devuelve el cuerpo de la solicitud sin tratar
     **/
    public function rawData(): ?string
    {
        return $this->content;
    }

    /**
     * Obtiene ficheros subidos al servidor
     */
    public function files(): array
    {
        return $this->files ??  [];
    }

    /**
     * Crea una instancia del objeto UploadedFile
     */
    private static function createUploadedFile(array $value): UploadedFile
    {
        return new UploadedFile(
            $value["name"],
            $value["type"],
            $value["tmp_name"],
            $value["error"],
            $value["size"]
        );
    }

    /**
     * Normaliza archivos enviados por $_FILES
     */
    private function normalizeFiles(array $uploadFiles): void
    {
        //archivos
        foreach ($uploadFiles as $key => $file) {
            if (is_array($file['name'])) {
                foreach ($file['name'] as $i => $name) {
                    $this->files[$key][] = self::createUploadedFile(
                        [
                            'name'     => $file['name'][$i]      ?? null,
                            'type'     => $file['type'][$i]      ?? null,
                            'tmp_name' => $file['tmp_name'][$i]  ?? null,
                            'error'    => $file['error'][$i]     ?? null,
                            'size'     => $file['size'][$i]      ?? null,
                        ]
                    );
                }
            } else {
                $this->files[$key] = self::createUploadedFile($file);
            }
        }
    }
}
