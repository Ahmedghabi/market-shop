<?php

namespace App\Service\Chat;

use App\Entity\ChatbotConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ChatbotService
{
    private const OLLAMA_GENERATE_ENDPOINT = '/api/generate';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $ollamaBaseUrl,
        private bool $globalEnabled,
    ) {
    }

    public function isGloballyEnabled(): bool
    {
        return $this->globalEnabled;
    }

    public function generateResponse(ChatbotConfig $config, string $userMessage, array $history = []): string
    {
        if (!$this->globalEnabled) {
            return '';
        }

        $model = $this->resolveModel($config);

        $messages = $this->buildMessages($config, $userMessage, $history);

        $payload = [
            'model' => $model,
            'prompt' => $this->formatPrompt($messages),
            'stream' => false,
            'options' => [
                'temperature' => $config->getTemperature(),
                'num_predict' => $config->getMaxTokens(),
            ],
        ];

        if ($config->getSystemPrompt()) {
            $payload['system'] = $config->getSystemPrompt();
        }

        $response = $this->httpClient->request('POST', $this->ollamaBaseUrl.self::OLLAMA_GENERATE_ENDPOINT, [
            'json' => $payload,
            'timeout' => 60,
        ]);

        $data = $response->toArray();

        return $data['response'] ?? '';
    }

    public function resolveModel(ChatbotConfig $config): string
    {
        $planModel = $config->getBoutique()->getCurrentSubscription()?->getSubscriptionPlan()?->getChatbotModel();
        if (null !== $planModel) {
            return $planModel;
        }

        return $config->getModel();
    }

    private function buildMessages(ChatbotConfig $config, string $userMessage, array $history): array
    {
        $messages = [];

        foreach ($history as $msg) {
            $role = match ($msg['senderType'] ?? 'user') {
                'bot', 'admin' => 'assistant',
                default => 'user',
            };
            if (!empty($msg['content'])) {
                $messages[] = ['role' => $role, 'content' => $msg['content']];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    private function formatPrompt(array $messages): string
    {
        $lines = [];
        foreach ($messages as $msg) {
            $prefix = 'user' === $msg['role'] ? 'Client' : 'Assistant';
            $lines[] = sprintf('%s: %s', $prefix, $msg['content']);
        }
        $lines[] = 'Assistant:';

        return implode("\n\n", $lines);
    }
}
