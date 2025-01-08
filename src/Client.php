<?php

namespace Mk4U\Http;


/**
 * Client class
 */
class Client
{
    private \CurlHandle $curl;
    private Request $request;
    private const METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        /*'HEAD',
        'OPTIONS',
        'PATCH'*/
    ];

    /**
     * Inicializa Curl
     */
    public function __construct(private array $optionDefault = [])
    {
        if (!\extension_loaded('curl')) {
            throw new \RuntimeException('The "curl" extension is not installed.');
        }

        $this->optionDefault = array_merge([
            'timeout' => 30,
            'max_redirects' => 10,
            'http_version' => \CURL_HTTP_VERSION_1_1
        ], $optionDefault);

        $this->curl = \curl_init();
    }

    /**
     * Cierra session Curl
     */
    public function __destruct()
    {
        \curl_close($this->curl);
        unset($this->curl);
    }

    /**
     * Envia la peticion
     */
    public function request(string $method, string $uri, array $options = []): Response
    {
        //Obtiene la peticion
        $this->request = new Request($method, $uri, $options['headers'] ?? []);

        //Verifica el metodo
        if (!in_array($this->request->getMethod(), self::METHODS)) {
            throw new \InvalidArgumentException(
                sprintf('Http method "%s" not implemented.', $method)
            );
        }

        //Establece las opciones
        $this->setOptions(array_merge($this->optionDefault, $options));

        //Ejecuta cURL y retorna la respuesta la respuesta
        return $this->response(\curl_exec($this->curl));
    }

    /**
     * Recibe la respuesta
     */
    public function response(string|bool $response): Response
    {
        if (!$response) {
            throw new \Error(
                sprintf(
                    'cURL error #%d: %s [%s]',
                    \curl_errno($this->curl),
                    \curl_error($this->curl),
                    'see https://curl.haxx.se/libcurl/c/libcurl-errors.html'
                )
            );
        }


        // Obtiene el código de estado HTTP
        $statusCode = \curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        // Obtiene el tamaño de los encabezados
        //$headerSize = \curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        // Extrae los encabezados de la respuesta
        /* $headers = substr($response, 0, $headerSize);

        // Extrae el cuerpo de la respuesta
        $body = substr($response, $headerSize);*/


        // Devuelve un nuevo objeto Response
        return new Response(
            $response,
            Status::tryFrom($statusCode),
            $this->request->getHeaders()
        );
    }

    /**
     * Ejecuta peticiones para cada metodo especifico
     */
    public function __call($method, $arguments): Response
    {
        if (empty($arguments)) {
            throw new \InvalidArgumentException("Error processing empty arguments.");
        }

        return $this->request($method, $arguments[0], $arguments[1] ?? []);
    }

    public function setOptions(array $options): void
    {
        $curlOptions = [
            // Default
            CURLOPT_RETURNTRANSFER => true,                         // Devuelve la respuesta en lugar de imprimir
            #CURLOPT_HEADER         => true,                         // Devuelve los encabezados
            CURLOPT_MAXREDIRS      => $options['max_redirects'],    // Limita las redirecciones a 10.
            CURLOPT_CONNECTTIMEOUT => $options['timeout'],          // Tiempo máximo para conectar.
            CURLOPT_TIMEOUT        => $options['timeout'],          // Tiempo máximo para recibir respuesta
            CURLOPT_HTTP_VERSION   => $options['http_version'],     // HTTP Version
            CURLOPT_USERAGENT      => 'Mk4U',                       // Define el User-Agent

            CURLOPT_FOLLOWLOCATION => true,                         // Permite seguir redirecciones.
            CURLOPT_ENCODING       => "",                           // Maneja las codificaciones.
            CURLOPT_AUTOREFERER    => true,                         // Establece Referer en redirecciones.




            CURLOPT_URL            => $this->request->getUri(),     // Establece la URL
            CURLOPT_HTTPHEADER     => $this->request->getHeaders(), // Cabeceras HTTP
            CURLOPT_CUSTOMREQUEST  => $this->request->getMethod(),  // Metodo HTTP a usar



            CURLOPT_SSL_VERIFYHOST => 0,            // No verifica el nombre del host en el certificado SSL.
            CURLOPT_SSL_VERIFYPEER => false,        // No verifica el certificado SSL.
            CURLOPT_VERBOSE        => 1               // Activa la salida detallada para depuración.
        ];

        // Obtener cabecera HTTP
        \curl_setopt(
            $this->curl,
            CURLOPT_HEADERFUNCTION,
            function ($curl, $header) {
                $len = strlen($header);
                $header = explode(':', $header, 2);

                //ignore invalid headers
                if (count($header) < 2) return $len;

                $headers[(trim($header[0]))] = trim($header[1]);
                $this->request->setHeaders($headers);

                return $len;
            }
        );

        // Establecer las opciones de cURL
        if (\curl_setopt_array($this->curl, $curlOptions) === false) {
            throw new \RuntimeException("Failed to set cURL options.");
        }
    }
}
