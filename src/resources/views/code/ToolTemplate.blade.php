{!! '<?php' !!}
namespace App\Tools;

class {{ $class_name }}
{
    public const NAME = '{{ $class_name }}';
    public const DESCRIPTION = 'Description for {{ $class_name }} tool. The description is important as it is used to instruct
	the LLM. Update it, including detailed description for every parameter';

    // Make sure every parameter is a typed primitive. Return is always a string
    public static function run(): string
    {
        // Implement the tool logic here
    }
}
