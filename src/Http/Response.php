<?php

namespace Sentgine\Ray\Http;

/**
 * Class Response
 *
 * Represents an HTTP response.
 */
class Response
{
    /** Common content types. */
    public const CONTENT_TYPE_HTML = 'text/html; charset=utf-8';
    public const CONTENT_TYPE_JSON = 'application/json';
    public const CONTENT_TYPE_PLAIN = 'text/plain';
    public const CONTENT_TYPE_XML = 'application/xml';
    public const CONTENT_TYPE_CSS = 'text/css';
    public const CONTENT_TYPE_JAVASCRIPT = 'application/javascript';
    public const CONTENT_TYPE_JPEG = 'image/jpeg';
    public const CONTENT_TYPE_PNG = 'image/png';
    public const CONTENT_TYPE_GIF = 'image/gif';
    public const CONTENT_TYPE_AUDIO = 'audio/*';
    public const CONTENT_TYPE_VIDEO = 'video/*';
    public const CONTENT_TYPE_PDF = 'application/pdf';
    public const CONTENT_TYPE_CSV = 'text/csv';
    public const CONTENT_TYPE_ZIP = 'application/zip';

    /**
     * Response constructor.
     *
     * @param mixed $content The content of the response.
     * @param int $status The HTTP status code of the response (default: 200).
     * @param array $headers The HTTP headers of the response (default: empty array).
     */
    public function __construct(
        private $content = '',
        private int $status = 200,
        private array $headers = []
    ) {
    }

    /**
     * Send the HTTP response to the client.
     */
    public function send(): void
    {
        // Set headers before sending content
        $this->setHeaders();
        echo $this->content;
    }

    /**
     * Set headers based on the content type.
     */
    private function setHeaders(): void
    {
        // Allow content type override
        if (!isset($this->headers['Content-Type'])) {
            if (is_array($this->content) || is_object($this->content)) {
                $this->headers['Content-Type'] = 'application/json';
            } else {
                $this->headers['Content-Type'] = 'text/html; charset=utf-8';
            }
        }

        // Set other headers
        foreach ($this->headers as $header => $value) {
            header("$header: $value");
        }
    }

    /**
     * Set the content type explicitly.
     *
     * @param string $contentType The content type to set.
     * @return $this
     */
    public function setContentType(string $contentType): self
    {
        $this->headers['Content-Type'] = $contentType;
        return $this;
    }

    /**
     * Convert the content of the response to JSON format.
     *
     * @return $this The Response object with content encoded as JSON.
     * @throws \RuntimeException If content encoding fails.
     */
    public function json(): self
    {
        // Set content type to JSON
        $this->headers['Content-Type'] = self::CONTENT_TYPE_JSON;

        $jsonContent = json_encode($this->content);

        // Check if JSON encoding failed
        if ($jsonContent === false) {
            throw new \RuntimeException('Failed to encode content as JSON.');
        }

        // Update the content with the JSON-encoded content
        $this->content = $jsonContent;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to plain text.
     *
     * @return $this The Response object with content type set to plain text.
     */
    public function plainText(): self
    {
        // Set content type to plain text
        $this->headers['Content-Type'] = self::CONTENT_TYPE_PLAIN;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to XML.
     *
     * @return $this The Response object with content type set to XML.
     */
    public function xml(): self
    {
        // Set content type to XML
        $this->headers['Content-Type'] = self::CONTENT_TYPE_XML;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to CSS.
     *
     * @return $this The Response object with content type set to CSS.
     */
    public function css(): self
    {
        // Set content type to CSS
        $this->headers['Content-Type'] = self::CONTENT_TYPE_CSS;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to JavaScript.
     *
     * @return $this The Response object with content type set to JavaScript.
     */
    public function javascript(): self
    {
        // Set content type to JavaScript
        $this->headers['Content-Type'] = self::CONTENT_TYPE_JAVASCRIPT;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to audio/mpeg.
     *
     * @return $this The Response object with content type set to audio/mpeg.
     */
    public function audioMpeg(): self
    {
        // Set content type to audio/mpeg
        $this->headers['Content-Type'] = 'audio/mpeg';

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to video/mp4.
     *
     * @return $this The Response object with content type set to video/mp4.
     */
    public function videoMp4(): self
    {
        // Set content type to video/mp4
        $this->headers['Content-Type'] = 'video/mp4';

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to image/jpeg.
     *
     * @return $this The Response object with content type set to image/jpeg.
     */
    public function jpeg(): self
    {
        // Set content type to image/jpeg
        $this->headers['Content-Type'] = 'image/jpeg';

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to image/png.
     *
     * @return $this The Response object with content type set to image/png.
     */
    public function png(): self
    {
        // Set content type to image/png
        $this->headers['Content-Type'] = 'image/png';

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to image/gif.
     *
     * @return $this The Response object with content type set to image/gif.
     */
    public function gif(): self
    {
        // Set content type to image/gif
        $this->headers['Content-Type'] = 'image/gif';

        // Return the updated Response object
        return $this;
    }


    /**
     * Set the content type to audio.
     *
     * @return $this The Response object with content type set to audio.
     */
    public function audio(): self
    {
        // Set content type to audio
        $this->headers['Content-Type'] = self::CONTENT_TYPE_AUDIO;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to video.
     *
     * @return $this The Response object with content type set to video.
     */
    public function video(): self
    {
        // Set content type to video
        $this->headers['Content-Type'] = self::CONTENT_TYPE_VIDEO;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to PDF.
     *
     * @return $this The Response object with content type set to PDF.
     */
    public function pdf(): self
    {
        // Set content type to PDF
        $this->headers['Content-Type'] = self::CONTENT_TYPE_PDF;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to CSV.
     *
     * @return $this The Response object with content type set to CSV.
     */
    public function csv(): self
    {
        // Set content type to CSV
        $this->headers['Content-Type'] = self::CONTENT_TYPE_CSV;

        // Return the updated Response object
        return $this;
    }

    /**
     * Set the content type to ZIP.
     *
     * @return $this The Response object with content type set to ZIP.
     */
    public function zip(): self
    {
        // Set content type to ZIP
        $this->headers['Content-Type'] = self::CONTENT_TYPE_ZIP;

        // Return the updated Response object
        return $this;
    }
}
