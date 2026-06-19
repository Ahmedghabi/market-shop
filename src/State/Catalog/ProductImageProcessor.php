<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Catalog\ProductImageResource;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Repository\ProductRepository;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/** @implements ProcessorInterface<ProductImageResource> */
final readonly class ProductImageProcessor implements ProcessorInterface
{
    public function __construct(
        private ProductRepository $products,
        private EntityManagerInterface $em,
        private ImageService $imageService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ProductImageResource
    {
        $productId = $uriVariables['productId'] ?? '';
        $product = $this->products->find($productId);

        if (!$product instanceof Product) {
            return null;
        }

        $file = $context['request']->files->get('file');
        $position = (int) $context['request']->request->get('position', 0);
        $alt = $context['request']->request->get('alt');

        if ($file instanceof UploadedFile && $file->isValid()) {
            $paths = $this->imageService->uploadAndResize($file, 'products');

            $productImage = new ProductImage(
                product: $product,
                url: $paths['url'],
                position: $position,
                alt: $alt,
            );
            $productImage->setSmallUrl($paths['smallUrl']);
            $productImage->setLargeUrl($paths['largeUrl']);

            $this->em->persist($productImage);
            $this->em->flush();

            $output = new ProductImageResource();
            $output->id = (string) $productImage->getId();
            $output->productId = (string) $product->getId();
            $output->url = $paths['url'];
            $output->smallUrl = $paths['smallUrl'];
            $output->largeUrl = $paths['largeUrl'];
            $output->position = $position;
            $output->alt = $alt;

            return $output;
        }

        return null;
    }
}
