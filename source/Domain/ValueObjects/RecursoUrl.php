<?php

namespace Domain\ValueObjects;

class RecursoUrl
{
    private string $value;

    public function __construct(string $url)
    {
        $this->validate($url);
        $this->value = $url;
    }

    private function validate(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('URL inválida');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'])) {
            throw new \InvalidArgumentException('URL deve usar protocolo HTTP ou HTTPS');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return parse_url($this->value, PHP_URL_HOST) ?? '';
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
