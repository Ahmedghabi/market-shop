<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Config\CacheConfig;
use App\Entity\ChatbotConfig;
use App\Factory\RedisFactory;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ChatbotConfigProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private BoutiqueRepository $boutiqueRepository,
        private BoutiqueContext $boutiqueContext,
        private RedisFactory $redisFactory,
        private CacheConfig $cacheConfig,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $boutiqueId = $uriVariables['boutiqueId'] ?? $this->boutiqueContext->getBoutiqueId();
        if (null === $boutiqueId) {
            throw new \RuntimeException('Boutique context required');
        }

        $boutique = $this->boutiqueRepository->find((string) $boutiqueId);
        if (null === $boutique) {
            throw new \RuntimeException('Boutique not found');
        }

        $repo = $this->em->getRepository(ChatbotConfig::class);
        $config = $repo->findOneBy(['boutique' => $boutique]);

        if ($data instanceof ChatbotConfig) {
            $config = $data;
        } elseif (null === $config) {
            $config = new ChatbotConfig($boutique);
            $this->em->persist($config);
        }

        if (isset($data->model)) {
            $config->setModel($data->model);
        }
        if (isset($data->systemPrompt)) {
            $config->setSystemPrompt($data->systemPrompt);
        }
        if (isset($data->temperature)) {
            $config->setTemperature($data->temperature);
        }
        if (isset($data->maxTokens)) {
            $config->setMaxTokens($data->maxTokens);
        }
        if (isset($data->isEnabled)) {
            $config->setIsEnabled($data->isEnabled);
        }

        $this->em->flush();

        $this->invalidateCache($boutiqueId);

        return $config;
    }

    private function invalidateCache(string $boutiqueId): void
    {
        try {
            $redis = $this->redisFactory->create();
            $redis->del('shop.'.$boutiqueId.'.chatbot_config');
        } catch (\RedisException) {
        }
    }
}
