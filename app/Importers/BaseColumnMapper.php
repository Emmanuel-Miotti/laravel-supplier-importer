<?php

namespace App\Importers;

use App\Contracts\ColumnMappingContract;

abstract class BaseColumnMapper implements ColumnMappingContract
{
    // Cada mapper hijo define sus propios aliases
    abstract protected function aliases(): array;

    public function resolveFromHeader(array $headers): array
    {
        $resolved = [];

        foreach ($this->aliases() as $field => $aliasList) { 
        // Por cada campo que necesito resolver, busco entre sus aliases cuál es el índice que le corresponde en el Excel
            foreach ($headers as $index => $header) {
                foreach ($aliasList as $alias) {
                    if ($this->normalize($header) === $this->normalize($alias)) {
                        $resolved[$field] = $index;
                        break 2;
                    }
                }
            }
        }

        return $resolved;
    }

    // Normalizo para no romper por mayúsculas o espacios raros
    protected function normalize(string $value): string
    {
        return preg_replace('/\s+/', ' ', strtolower(trim($value)));
    }
}