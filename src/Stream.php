<?php

namespace Mk4U\Http;

use RuntimeException;

/**
 * Describe un flujo de datos.
 *
 * Normalmente, una instancia envolverá una secuencia PHP; esta interfaz proporciona un 
 * resumen de las operaciones más comunes, incluida la serialización de toda la 
 * secuencia a una cadena.
 */
class Stream
{
    //private const MODE = ['w', 'wb', 'wt', 'r+', 'r+b', 'r+t', 'w+', 'w+b', 'w+t', 'a+', 'a+b', 'a+t', 'a', 'c', 'c+', 'r', 'rb', 'rt'];

    public function __construct(private mixed $stream, string $mode = 'r')
    {
        $this->stream = is_resource($stream) ? $stream : fopen($stream, $mode);
    }

    /**
     * Lee todos los datos de la secuencia en una cadena, desde el principio hasta el final.
     *
     * Advertencia: Esto podría intentar cargar una gran cantidad de datos en la memoria.
     *
     * Este método NO DEBE generar una excepción para cumplir con PHP operaciones 
     * de fundición de cuerdas.
     */
    public function __toString(): string
    {
        if (isset($this->stream)) {
            $this->seek(0);
            return $this->getContents();
        }

        return '';
    }

    /**
     * Cierra la transmisión y cualquier recurso subyacente.
     */
    public function close(): void
    {
        fclose($this->stream);
    }

    /**
     * Separa los recursos subyacentes de la secuencia.
     *
     * Una vez desconectada la transmisión, ésta queda en un estado inutilizable.
     */
    public function detach(): void
    {
        $this->stream = null;
    }

    /**
     * Obtenga el tamaño de la transmisión si lo conoce.
     */
    public function getSize(): ?int
    {
        return fstat($this->stream)['size'] ?? null;
    }

    /**
     *  Devuelve la posición actual del puntero de lectura/escritura del archivo.
     */
    public function tell(): int
    {
        return ftell($this->stream);
    }

    /**
     * Devuelve verdadero si el puntero está al final de la transmisión.
     */
    public function eof(): bool
    {
        return feof($this->stream);
    }

    /**
     * Devuelve si la transmisión es buscable o no.
     */
    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable') ?? false;
    }

    /**
     *Buscar una posición en la flujo.
     *
     * @see http://www.php.net/manual/es/function.fseek.php
     * 
     * @param int $offset Desplazamiento de flujo
     * @param int $wherece Especifica cómo se calculará la posición del cursor basado 
     * en el desplazamiento de búsqueda.
     * 
     * SEEK_SET: Establecer posición igual a bytes de desplazamiento
     * SEEK_CUR: establece la posición en la ubicación actual más el desplazamiento
     * SEEK_END: establece la posición al final de la transmisión más el desplazamiento.
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        fseek($this->stream, $offset, $whence);
    }

    /**
     *Buscar hasta el inicio del arroyo.
     *
     * Si la secuencia no se puede buscar, este método generará una excepción;
     * en caso contrario, realizará una seek(0).
     *
     * @see https://www.php.net/manual/es/function.rewind.php
     */
    public function rewind(): void
    {
        rewind($this->stream);
    }

    /**
     * Devuelve si se puede escribir en la secuencia o no.
     */
    public function isWritable(): bool
    {
        $readable = ['r', 'rb', 'rt'];

        return in_array($this->getMetadata('mode'), $readable) ? false : true;
    }

    /**
     * Escribe datos en la secuencia.
     *
     * @param string $data La cadena que se va a escribir.
     * @return int Devuelve el número de bytes escritos en la secuencia.
     */
    public function write(string $data): int|false
    {
        return fwrite($this->stream, $data);
    }

    /**
     * Devuelve si la transmisión es legible o no.
     */
    public function isReadable(): bool
    {
        $writable = ['w', 'wb', 'wt', 'c', 'a'];

        return in_array($this->getMetadata('mode'), $writable) ? false : true;
    }

    /**
     * Leer datos de la transmisión.
     *
     * @param int $length Lee hasta $length bytes del objeto y regresa
     *     a ellos. Se pueden devolver menos de $length bytes si la secuencia subyacente
     * la llamada devuelve menos bytes.
     * @return string Devuelve los datos leídos de la secuencia o una cadena vacía si no 
     * hay bytes disponibles.
     */
    public function read(int $length): string|false
    {
        if ($this->getSize() == 0) {
            return '';
        }
        return fread($this->stream, $length);
    }

    /**
     * Devuelve el contenido restante en una cadena
     *
     * @return string
     * @throws \RuntimeException si no se puede leer.
     */
    public function getContents(): string
    {
        if ($this->isReadable()) {
            throw new RuntimeException("No se puede leer");
        }

        return stream_get_contents($this->stream);
    }

    /**
     * Obtenga metadatos de transmisión como una matriz asociativa o recupere una clave específica.
     *
     * Las claves devueltas son idénticas a las claves devueltas por PHP
     * Función stream_get_meta_data().
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Metadatos específicos para recuperar.
     * @return array|mixed|null Devuelve una matriz asociativa si no hay ninguna clave
     *     proporcionó. Devuelve un valor de clave específico si se proporciona una clave y el
     *     se encuentra el valor, o nulo si no se encuentra la clave.
     */
    public function getMetadata(?string $key = null): mixed
    {
        $meta = stream_get_meta_data($this->stream);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    /**
     * Cerrar flujo al destruir la clase
     */
    public function __destruct()
    {
        $this->close();
    }
}
