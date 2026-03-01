<?php

namespace Domain\ValueObjects;

class RecursoTipo
{
    public const VIDEO = 'video';
    public const PDF = 'pdf';
    public const LINK = 'link';

    private string $value;

    public function __construct(string $tipo)
    {
        $this->validate($tipo);
        $this->value = $tipo;
    }

    private function validate(string $tipo): void
    {
        $valid = [self::VIDEO, self::PDF, self::LINK];
        
        if (!in_array($tipo, $valid)) {
            throw new \InvalidArgumentException(
                "Tipo inválido. Valores aceitos: " . implode(', ', $valid)
            );
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isVideo(): bool
    {
        return $this->value === self::VIDEO;
    }

    public function isPdf(): bool
    {
        return $this->value === self::PDF;
    }

    public function isLink(): bool
    {
        return $this->value === self::LINK;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function all(): array
    {
        return [self::VIDEO, self::PDF, self::LINK];
    }
}
