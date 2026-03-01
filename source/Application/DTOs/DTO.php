<?php

namespace Application\DTOs;

abstract class DTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $data): static
    {
        $dto = new static();
        
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }
        
        return $dto;
    }
}
