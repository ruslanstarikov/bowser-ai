<?php

namespace Ruslanstarikov\BowserAi\Utils;

use InvalidArgumentException;

class ToolsCall
{
    public static function run(string $tool_name, array $tool_arguments, array $tool_map): string
    {
        if (!isset($tool_map[$tool_name])) {
            throw new InvalidArgumentException("Tool '$tool_name' not found in map.");
        }

        [$tool_class, $tool_method] = $tool_map[$tool_name];

        if (!method_exists($tool_class, $tool_method)) {
            throw new InvalidArgumentException("Method '$tool_method' does not exist in class '$tool_class'.");
        }

        return $tool_class::$tool_method(...$tool_arguments);
    }
}

