<?php

namespace App\State\Suggestion;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Suggestion\SuggestionCategoryInput;
use App\Dto\Suggestion\SuggestionCategoryOutput;
use App\Entity\SuggestionCategory;
use App\Repository\SuggestionCategoryRepository;
use App\Service\Audit\AuditLogService;
use App\Service\Suggestion\SuggestionAccessService;
use App\Service\Suggestion\SuggestionOutputMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SuggestionCategoryProcessor implements ProcessorInterface
{
    public function __construct(private SuggestionCategoryRepository $categories, private SuggestionAccessService $access, private SuggestionOutputMapper $mapper, private AuditLogService $audit, private EntityManagerInterface $em, private RequestStack $requestStack)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?SuggestionCategoryOutput
    {
        if (!$this->access->isSuperAdmin()) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Only super admins can manage suggestion categories.');
        }
        $this->access->assertPermission('suggestion.category.manage');
        $entity = isset($uriVariables['id']) ? $this->categories->find($uriVariables['id']) : null;
        if ($operation instanceof Delete) {
            if (!$entity) {
                throw new NotFoundHttpException('Suggestion category not found.');
            }
            $this->em->remove($entity);
            $this->em->flush();
            $this->audit->log($this->access->requireUser()->getUserIdentifier(), 'ROLE_SUPER_ADMIN', 'suggestion.category.delete', 'SuggestionCategory', (string) $entity->getId(), null, $this->requestStack->getCurrentRequest()?->getClientIp());

            return null;
        }
        if (!$operation instanceof Post && !$entity) {
            throw new NotFoundHttpException('Suggestion category not found.');
        }
        if (!$data instanceof SuggestionCategoryInput) {
            throw new BadRequestHttpException('Invalid suggestion category payload.');
        }
        $created = null === $entity;
        if (!$entity) {
            if (null === $data->name || null === $data->slug) {
                throw new BadRequestHttpException('Name and slug are required.');
            }
            if ($this->categories->slugExists($data->slug)) {
                throw new ConflictHttpException('Category slug already exists.');
            }
            $entity = new SuggestionCategory($data->name, $data->slug);
            $this->em->persist($entity);
        } else {
            if ($this->categories->slugExists((string) $data->slug, (string) $entity->getId())) {
                throw new ConflictHttpException('Category slug already exists.');
            }
            if (null !== $data->name) {
                $entity->setName($data->name);
            }
            if (null !== $data->slug) {
                $entity->setSlug($data->slug);
            }
        }
        if (null !== $data->description) {
            $entity->setDescription($data->description);
        }
        if (null !== $data->isActive) {
            $entity->setIsActive($data->isActive);
        }
        if (null !== $data->position) {
            $entity->setPosition($data->position);
        }
        $this->em->flush();
        $this->audit->log($this->access->requireUser()->getUserIdentifier(), 'ROLE_SUPER_ADMIN', $created ? 'suggestion.category.create' : 'suggestion.category.update', 'SuggestionCategory', (string) $entity->getId(), null, $this->requestStack->getCurrentRequest()?->getClientIp());

        return $this->mapper->category($entity);
    }
}
