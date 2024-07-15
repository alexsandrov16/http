<?php

namespace Mk4U\Http;

/**
 * Response class
 * 
 * 
 * 
 * Representación de una respuesta saliente del lado del servidor.
 * 
 * Según la especificación HTTP, esta interfaz incluye propiedades para cada uno de los siguientes:
 * 
 * - Versión del protocolo
 * - Código de estado y frase de motivo
 * - Encabezados
 * - Cuerpo del mensaje
 * 
 * Las respuestas se consideran inmutables; todos los métodos que puedan cambiar de estado DEBEN implementarse 
 * de manera que conserven el estado interno del mensaje actual y devuelvan una instancia que contenga 
 * el estado cambiado.
 */
class Response
{
    /** @param int código de estado HTTP*/
    protected int $code;

    /** @param string frase de motivo de respuesta asociada con el código de estado*/
    protected string $phrase;

    /** @param mixed cuerpo del mensaje http*/
    protected mixed $body;

    use Headers;

    public function __construct(mixed $content = "", Status|array $status = Status::Ok, array $headers = [], string $version = null)
    {

        //version protocolo
        $this->setProtocolVersion($version);

        if (is_array($status)) {
            //especifica el codigo de estado con la frase de motivo
            $this->setStatus($status[0], $status[1]);
        } else {
            //especifica el codigo de estado con la frase de motivo por defecto
            $this->setStatus($status->value);
        }

        //establecer cabeceras
        $this->setHeaders($headers);

        //establecer cuerpo del mensaje
        $this->setBody($content);
    }

    public function __toString():string
    {
        return $this->send();
    }

    /**
     * Debuguear mensanje de la respuesta HTTP
     */
    public function __debugInfo(): array
    {
        return [
            "protocol" => $this->getprotocolVersion(),
            "code"     => $this->getStatusCode(),
            "phrase"   => $this->getReasonPhrase(),
            "headers"  => $this->getHeaders(),
            "body"     => $this->getBody()
        ];
    }

    /**
     * Obtiene el código de estado de respuesta.
     */
    public function getStatusCode(): int
    {
        return $this->code;
    }

    /**
     * Devuelve una instancia con el código de estado especificado y, opcionalmente, la frase de motivo.
     * 
     * Si no se especifica ninguna frase de motivo, las implementaciones PUEDEN optar por el valor predeterminado
     * a la frase de motivo recomendada por RFC 7231 o IANA para la respuesta
     * código de estado.
     * 
     * Este método DEBE implementarse de tal manera que conserve la
     * inmutabilidad del mensaje, y DEBE devolver una instancia que tenga la
     * Estado actualizado y frase de motivo.
     * 
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code El código de resultado entero de 3 dígitos que se establecerá.
     * @param string $reasonPhrase La frase de motivo a usar con el
     * código de estado proporcionado; si no se proporciona ninguno, las implementaciones PUEDEN
     * utilice los valores predeterminados como se sugiere en la especificación HTTP.
     * @return static
     * @throws \InvalidArgumentException Para argumentos de código de estado no válidos.
     */
    public function setStatus(int $code, string $reasonPhrase = ''):Response
    {
        if ($code < 100 || $code > 599) {
            throw new \InvalidArgumentException("Invalid status code argumentss");
        }

        $this->code = $code;
        $this->phrase = empty($reasonPhrase) ? Status::phrase($code) : $reasonPhrase;

        return clone $this;
    }

    /**
     * Obtiene la frase del motivo de la respuesta asociada al código de estado.
     * 
     * Porque una frase de motivo no es un elemento obligatorio en una respuesta
     * línea de estado, el valor de la frase de motivo PUEDE estar vacío. Implementaciones MAYO
     * elija devolver la frase de motivo recomendada por RFC 7231 predeterminada (o aquellas
     * incluido en el Registro de códigos de estado HTTP de IANA) para la respuesta
     * código de estado.
     * 
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Frase de motivo; debe devolver una cadena vacía si no hay ninguna presente.
     */
    public function getReasonPhrase():string
    {
        return $this->phrase;
    }

    /**
     * Devuelve cuerpo del mensaje
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * Establece cuerpo del mensaje
     */
    public function setBody(mixed $body): Response
    {
        $this->body = $body;
        return clone $this;
    }

    /**
     * Envia el mensaje HTTP
     */
    protected function send(): string
    {
        header($this->getProtocolVersion() . ' ' . $this->getStatusCode() . ' ' . $this->getReasonPhrase());

        foreach ($this->getHeaders() as $name => $value) {
            header("$name: $value");
        }

        return $this->getBody();
    }

    /**
     * Devuelve cuerpo del mensaje como JSON
     */
    public static function json(array|string $content, Status|array $status = Status::Ok, array $headers = []): Response
    {
        $headers['content-type'] = 'application/json';
        return new static(
            is_string($content) ? $content : json_encode($content,JSON_PRETTY_PRINT),
            $status,
            $headers);
    }

    /**
     * Devuelve cuerpo del mensaje como texto plano
     */
    public static function plain(string $content, Status|array $status = Status::Ok, array $headers = []): Response
    {
        $headers['content-type'] = 'text/plain';
        return new static($content, $status, $headers);
    }

    /**
     * Devuelve cuerpo del mensaje como HTML
     */
    public static function html(string $content, Status|array $status = Status::Ok, array $headers = []): Response
    {
        $headers['content-type'] = 'text/html';
        return new static($content, $status, $headers);
    }

    /**
     * Devuelve cuerpo del mensaje como XML
     */
    public static function xml(string $content, Status|array $status = Status::Ok, array $headers = []): Response
    {
        $headers['content-type'] = 'application/xml';
        return new static($content, $status, $headers);
    }
}
