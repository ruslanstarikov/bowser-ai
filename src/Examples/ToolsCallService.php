<?php

namespace Ruslanstarikov\BowserAi\Examples;

use Illuminate\Support\Facades\Config;
use OpenAI;
use OpenAI\Client;
use Ruslanstarikov\BowserAi\Utils\ToolsCall;
use Ruslanstarikov\BowserAi\Utils\ToolsGenerator;

class ToolsCallService
{
	private Client $client;
	private array $messages = [];

	public function __construct()
	{
		$key = Config::get('bowser-ai.api_key');
		$this->client = OpenAI::client($key);
	}

	public function run(?string $system_message, string $user_input, array $tools, $model = 'gpt-4o-mini'): array
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
				'model' => $model,
				'messages' => $this->messages,
				'tools' => $tool_schemas
			]);

			$message = $result->choices[0]->message ?? null;
			if (!$message) {
				return ["error" => 'No response from AI'];
			}
			if ($message->role === 'assistant' && $message->content === null) {
				$arrMessage = $message->toArray();
				$arrMessage['content'] = '';
				$this->messages[] = $arrMessage;
			}

			if (empty($message->toolCalls)) {
				$this->messages[] = $message;
				return $this->messages;
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

