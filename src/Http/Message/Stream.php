<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

class Stream implements StreamInterface
{
    /** @var resource|null */
    private $stream;
    
    /** @var bool */
    private $seekable;
    
    /** @var bool */
    private $readable;
    
    /** @var bool */
    private $writable;
    
    /** @var array|null */
    private $meta;
    
    /** @var int|null */
    private $size;

    public function __construct($stream = null)
    {
        if ($stream !== null) {
            $this->stream = $stream;
            $this->detachMetadata();
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function close(): void
    {
        if ($this->stream) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach()
    {
        $result = $this->stream;
        $this->stream = null;
        $this->size = $this->seekable = $this->readable = $this->writable = null;
        
        return $result;
    }

    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!$this->stream) {
            return null;
        }

        $stats = fstat($this->stream);
        return $this->size = $stats['size'] ?? null;
    }

    public function tell(): int
    {
        if (!$this->stream) {
            throw new \RuntimeException('Stream is detached');
        }
        
        $result = ftell($this->stream);
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }
        
        return $result;
    }

    public function eof(): bool
    {
        return !$this->stream || feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->stream) {
            throw new \RuntimeException('Stream is detached');
        }
        
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }
        
        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position ' . $offset);
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write($string): int
    {
        if (!$this->stream) {
            throw new \RuntimeException('Stream is detached');
        }
        
        if (!$this->isWritable()) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }
        
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }
        
        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read($length): string
    {
        if (!$this->stream) {
            throw new \RuntimeException('Stream is detached');
        }
        
        if (!$this->isReadable()) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        
        $result = fread($this->stream, $length);
        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream');
        }
        
        return $result;
    }

    public function getContents(): string
    {
        if (!$this->stream) {
            throw new \RuntimeException('Stream is detached');
        }
        
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        
        return $contents;
    }

    public function getMetadata($key = null)
    {
        if (!$this->stream) {
            return $key ? null : [];
        }
        
        if ($key) {
            return $this->meta[$key] ?? null;
        }
        
        return $this->meta;
    }

    private function detachMetadata(): void
    {
        if (!$this->stream) {
            return;
        }
        
        $this->meta = stream_get_meta_data($this->stream);
        $this->seekable = $this->meta['seekable'] ?? false;
        $this->readable = (bool) (strstr($this->meta['mode'], 'r') || strstr($this->meta['mode'], '+'));
        $this->writable = (bool) (strstr($this->meta['mode'], 'w') || 
                              strstr($this->meta['mode'], 'a') || 
                              strstr($this->meta['mode'], '+'));
    }

    /**
     * Create a new stream from a string.
     *
     * @param string $content String content.
     * @return static
     */
    public static function createFromString(string $content = ''): self
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        rewind($resource);
        
        return new self($resource);
    }
}
