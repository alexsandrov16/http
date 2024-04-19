<?php

namespace Mk4U\Http;

/**
 * Headers Trait
 */
trait Headers
{
    /** @param string version del protocolo http*/
    protected string $version = 'HTTP/1.1';

    /** @param array headers del mensaje http*/
    protected array $headers = [];

    /**
     * Version del Protocolo Http
     */
    protected function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * Version del Protocolo Http
     */
    protected function setProtocolVersion(?string $version = null): static
    {
        if (preg_match('/^[1-3|(1.1)]$/', $version)) {
            $this->version = "HTTP/$version";
        }

        $version = strtoupper($version);
        if (preg_match('~HTTP~', $version)) {
            $this->version = $version;
        }

        return clone $this;
    }

    /**
     * Obtener todas las Cabeceras
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Mostrar cabecera
     */
    public function getHeader(string $name): string
    {
        if ($this->hasHeader($name)) {
            return $this->headers[$this->sanitizeHeader($name)];
        }
        return '';
    }

    /**
     * Verificar si existe una cabecera
     */
    public function hasHeader(string $name): bool
    {
        return key_exists($this->sanitizeHeader($name), $this->headers);
    }

    /**
     * Establecer cabecera
     */
    public function setHeader(string $name, string|array $value): static
    {
        $this->headers[$this->sanitizeHeader($name)] = $value;

        return clone $this;
    }

    /**
     * Establecer todas las cabeceras
     */
    public function setHeaders(array $headers): static
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return clone $this;
    }

    /**
     * Agregar cabecera
     * 
     * Si existe se agrega el valor al final
     */
    public function addHeader(string $name, string|array $value): static
    {
        if (!$this->hasHeader($name)) {
            return $this->setHeader($name, $value);
        }
        array_merge($this->headers[$this->sanitizeHeader($name)], [$name => $value]);
        return clone $this;
    }

    /**
     * Eliminar Cabecera
     */
    public function removeHeader(string $name): static
    {
        if ($this->hasHeader($name)) unset($this->headers[$this->sanitizeHeader($name)]);
        return clone $this;
    }

    /**
     * Estandarizar nombre de cabecera
     */
    private function sanitizeHeader(string $name): string
    {

        return str_replace(' ', '-', ucwords(str_replace(['-', '_'], ' ', strtolower($name))));
    }
}
