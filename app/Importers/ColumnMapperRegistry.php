<?php

namespace App\Importers;

use App\Contracts\ColumnMappingContract;
use InvalidArgumentException;

class ColumnMapperRegistry
{
    // Acá registro todos los mappers
    // Si agrego un proveedor nuevo solo lo agrego en esta lista
    private array $mapperClasses = [
        ProviderAColumnMapper::class,
        ProviderBColumnMapper::class,
    ];

    
    // Detecta automáticamente qué mapper usar según los headers del Excel
    // elige el que reconoce más columnas
     
    public function detect(array $headers, ?string $hint = null): ColumnMappingContract
    {
        if ($hint !== null) {
            return $this->findByName($hint);
        }

        $bestMapper = null;
        $bestScore  = -1;

        foreach ($this->mapperClasses as $class) {
            $mapper  = new $class();
            $score   = count($mapper->resolveFromHeader($headers));

            if ($score > $bestScore) {
                $bestScore  = $score;
                $bestMapper = $mapper;
            }
        }

        if ($bestMapper === null || $bestScore === 0) {
            throw new InvalidArgumentException(
                'No se reconoció ninguna columna del Excel. Registrá un mapper para este proveedor.'
            );
        }

        return $bestMapper;
    }

    // Para registrar un mapper nuevo en runtime 
    public function register(string $mapperClass): void
    {
        $this->mapperClasses[] = $mapperClass;
    }

    private function findByName(string $name): ColumnMappingContract
    {
        foreach ($this->mapperClasses as $class) {
            $mapper = new $class();
            if (strtolower($mapper->providerName()) === strtolower($name)) {
                return $mapper;
            }
        }

        throw new InvalidArgumentException("No hay mapper para el proveedor: {$name}");
    }
}