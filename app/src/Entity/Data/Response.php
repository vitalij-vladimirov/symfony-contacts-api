<?php
declare(strict_types=1);

namespace App\Entity\Data;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response extends HttpResponse
{
    public array $contentDecoded = [];

    public function __construct(HttpResponse $response)
    {
        parent::__construct($response->getContent(), $response->getStatusCode());

        $this->headers = $response->headers;
        $this->version = $response->version;
        $this->statusText = $response->statusText;
        $this->charset = $response->getCharset();
        self::$statusTexts = $response::$statusTexts;

        if (!empty($response->getContent())) {
            $this->contentDecoded = json_decode(
                $response->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
    }

    public function getHeaders(): ResponseHeaderBag
    {
        return $this->headers;
    }

    public function getStatusText(): string
    {
        return $this->statusText;
    }

    public static function getStatusTexts(): array
    {
        return self::$statusTexts;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getContentDecoded(): array
    {
        return $this->contentDecoded;
    }
}
