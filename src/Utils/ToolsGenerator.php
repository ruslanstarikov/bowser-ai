<?php

namespace Ruslanstarikov\BowserAi\Utils;

class ToolsGenerator
{
    public static function run(array $tools): array
    {
        $tool_schemas = [];
        $tools_map = [];
        foreach ($tools as $tool) {
            $tool_schema = FunctionSchemaGenerator::functionToSchema($tool);
            $tool_schemas[] = $tool_schema;
            $tools_map[$tool_schema['function']['name']] = $tool;
        }

        return [
            'map' => $tools_map,
            'schemas' => $tool_schemas
        ];
    }
}

