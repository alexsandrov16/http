<?php

namespace Mk4U\Http;


/**
 * Client class
 */
class Client
{
    private \CurlHandle $curl;
    private Request $request;
    private array $methods = [
        'get',
        'post',
        'put',
        'delete',
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
        $this->request = new Request($method, $uri, $options['headers'] ?? []);

        $this->setOptions(array_merge($this->optionDefault,$options));

        return $this->response(\curl_exec($this->curl));
    }

    /**
     * Recibe la respuesta
     */
    public function response(string|bool $response): Response
    {
        if (!$response) {
            throw new \Error(\curl_error($this->curl), \curl_errno($this->curl));
        }


        // HTTP status code
        $statusCode = \curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        // Headers
        $headerSize = \curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);

        //Body
        $body = substr($response, $headerSize);

        // Return a new Response object with the body, headers, and status code
        return new Response($body);
    }

    /**
     * Ejecuta peticiones para cada metodo especifico
     */
    public function __call($method, $arguments): Response
    {
        if (!in_array($method, $this->methods)) {
            throw new \InvalidArgumentException(
                sprintf('Http method "%s" not implemented.', $method)
            );
        }

        if (empty($arguments)) {
            throw new \InvalidArgumentException("Error processing empty arguments.");
        }

        return $this->request($method, $arguments[0], $arguments[1] ?? []);
    }

    public function setOptions(array $options): void
    {
        $curlOptions = [
            CURLOPT_URL            => $this->request->getUri(),     // Establece la URL
            CURLOPT_HTTPHEADER     => $this->request->getHeaders(), // Cabeceras HTTP
            CURLOPT_RETURNTRANSFER => true,                         // Devuelve la respuesta en lugar de imprimir
            #CURLOPT_HEADER => true, //Devuelve los encabezados
            CURLOPT_MAXREDIRS      => $options['max_redirects'],    // Limita las redirecciones a 10.
            CURLOPT_CONNECTTIMEOUT => $options['timeout'],          // Tiempo máximo para conectar.
            CURLOPT_TIMEOUT        => $options['timeout'],          // Tiempo máximo para recibir respuesta.

            CURLOPT_FOLLOWLOCATION => true,                         // Sigue las redirecciones
            CURLOPT_HTTP_VERSION   => $options['http_version'],     // HTTP Version


            CURLOPT_CUSTOMREQUEST => $this->request->getMethod(),

            CURLOPT_USERAGENT => 'Mk4U', // Define el User-Agent


            CURLOPT_FOLLOWLOCATION => true,         // Permite seguir redirecciones.
            CURLOPT_ENCODING       => "",           // Maneja automáticamente todas las codificaciones.

            CURLOPT_AUTOREFERER    => true,         // Establece automáticamente el Referer en redirecciones.

            CURLOPT_SSL_VERIFYHOST => 0,            // No verifica el nombre del host en el certificado SSL.
            CURLOPT_SSL_VERIFYPEER => false,        // No verifica el certificado SSL.
            CURLOPT_VERBOSE        => 1               // Activa la salida detallada para depuración.
        ];

        // Establecer las opciones de cURL
        if (curl_setopt_array($this->curl, $curlOptions) === false) {
            throw new \RuntimeException("Failed to set cURL options.");
        }
    }
}
