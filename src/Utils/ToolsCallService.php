<?php

namespace Ruslanstarikov\BowserAi\Utils;

use OpenAI;
use OpenAI\Client;

class ToolsCallService
{
    private Client $client;
    private array $messages = [];

    public function __construct(Client $client = null)
    {
        $this->client = $client;
        if($client === null) {
            $this->client = OpenAI::client(env('OPEN_AI_KEY'));
        }
    }

    public function run(?string $system_message, string $user_input, array $tools): string
    {
        $tools_array = ToolsGenerator::run($tools);
        $tool_schemas = $tools_array['schemas'];

        $tool_map = $tools_array['map'];

        if (!empty($system_message) && empty($this->messages)) {
            $this->messages[] = ['role' => 'system', 'content' => $system_message];
        }

        $this->messages[] = ['role' => 'user', 'content' => $user_input];

        while (true) {
            $result = $this->client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => $this->messages,
                'tools' => $tool_schemas
            ]);

            $message = $result->choices[0]->message ?? null;
            if (!$message) {
                return "No response from AI.";
            }
            if ($message->role === 'assistant' && $message->content === null) {
                $arrMessage = $message->toArray();
                $arrMessage['content'] = '';
                $this->messages[] = $arrMessage;
            }

            if (empty($message->toolCalls)) {
                return $message->content ?? '';
            }

            foreach ($message->toolCalls as $tool_call) {
                $tool_name = $tool_call->function->name;
                $function_arguments_json = $tool_call->function->arguments;
                $function_arguments = json_decode($function_arguments_json, true);

                $tool_content = ToolsCall::run($tool_name, $function_arguments, $tool_map);

                $tool_result = [
                    'role' => 'tool',
                    'tool_call_id' => $tool_call->id,
                    'content' => $tool_content,
                ];

                $this->messages[] = $tool_result;
            }
        }
    }
}

