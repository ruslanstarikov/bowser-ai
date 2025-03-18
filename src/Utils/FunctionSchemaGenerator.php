<?php

namespace Ruslanstarikov\BowserAi\Utils;

use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use InvalidArgumentException;

class FunctionSchemaGenerator
{
    public static function functionToSchema(callable $func): array
    {
        if (is_array($func)) {
            [$class, $method] = $func;
            $reflection = new ReflectionMethod($class, $method);
        } elseif (is_string($func)) {
            $reflection = new ReflectionFunction($func);
            $class = null;
        } else {
            throw new InvalidArgumentException("Unsupported callable type.");
        }

        $parameters = [];
        $required = [];

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $typeName = $type instanceof ReflectionNamedType ? self::mapPhpTypeToJson($type->getName()) : 'string';

            $jsonType = $type && $type->allowsNull()
                ? ['oneOf' => [['type' => $typeName], ['type' => 'null']]]
                : ['type' => $typeName];

            $parameters[$param->getName()] = $jsonType;

            if (!$param->isOptional()) {
                $required[] = $param->getName();
            }
        }

        $name = $class && defined("$class::NAME") ? constant("$class::NAME") : $reflection->getName();
        $description = $class && defined("$class::DESCRIPTION") ? constant("$class::DESCRIPTION") : '';

        return [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $description,
                'parameters' => [
                    'type' => 'object',
                    'properties' => $parameters,
                    'required' => $required,
                ],
            ],
        ];
    }

    private static function mapPhpTypeToJson(string $phpType): string
    {
        $typeMap = [
            'int' => 'number',
            'float' => 'number',
            'bool' => 'boolean',
            'string' => 'string',
            'array' => 'array',
            'object' => 'object',
            'null' => 'null',
        ];

        return $typeMap[$phpType] ?? 'string';
    }
}
