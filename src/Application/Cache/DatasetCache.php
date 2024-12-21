<?php

namespace App\Application\Cache;

use App\Application\Path\AppPathResolver;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\ItemInterface;

use function Symfony\Component\String\u;

final readonly class DatasetCache implements DatasetCacheInterface
{
    private FilesystemAdapter $cache;

    public function __construct(
        private AppPathResolver $appPathResolver,
        private ParameterBagInterface $parameterBag,
    ) {
        $this->cache = new FilesystemAdapter(
            defaultLifetime: $this->getDefaultLifetime(),
            directory: $this->appPathResolver->getDatasetCachePath(),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $datasetPath, callable $dataFactory, ?int $expiresAfter = null): mixed
    {
        $path = $this->transformPath($datasetPath);

        return $this->cache->get($path, function (ItemInterface $item) use ($expiresAfter, $dataFactory) {
            $item->expiresAfter($expiresAfter ?? $this->getDefaultLifetime());

            return $dataFactory();
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(string $datasetPath): bool
    {
        $path = $this->transformPath($datasetPath);

        return $this->cache->deleteItem($path);
    }

    public function clearExpired(): bool
    {
        return $this->cache->prune();
    }

    public function clearAll(): bool
    {
        return $this->cache->clear();
    }

    private function transformPath(string $datasetPath): string
    {
        return u($datasetPath)->replace('/', '_')->snake();
    }

    private function getDefaultLifetime(): int
    {
        return $this->parameterBag->get('dataset_default_cache_lifetime');
    }
}
