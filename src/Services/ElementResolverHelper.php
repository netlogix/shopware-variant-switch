<?php

namespace SasVariantSwitch\Services;

use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ElementResolverHelper
{
    public function __construct(
        #[Autowire(service: 'sales_channel.product.repository')]
        private readonly SalesChannelRepository $salesChannelProductRepository,
    ) {
    }

    public function addAssociationsToCriteriaList(?CriteriaCollection $criteriaCollection): ?CriteriaCollection
    {
        if ($criteriaCollection === null) {
            return null;
        }

        $criteriaCollectionContent = $criteriaCollection->all();
        $criteriaCollection = new CriteriaCollection();
        foreach ($criteriaCollectionContent as $definition => $content) {
            $criteria = current($content);
            $criteria->addAssociations(['options', 'manufacturer', 'media', 'cover']);
            $criteriaCollection->add(current(array_keys($content)), $definition, $criteria);
        }
        return $criteriaCollection;
    }

    public function convertResolveVariantProducts(?ProductCollection $products, SalesChannelContext $context): ?ProductCollection
    {
        if ($products === null) {
            return null;
        }

        return new ProductCollection($products->map(function(ProductEntity $product) use ($context) {
            if ($product->getParentId() === null && $product->getConfiguratorSettings() === null) {
                $children = $this->getChildProducts($product->getId(), $context);
                if ($children->count() > 0) {
                    return $children->first();
                }
            }
            return $product;
        }));
    }

    public function reloadProducts(ProductCollection $products, SalesChannelContext $context): ProductCollection
    {
        if ($products->count() === 0) {
            return $products;
        }

        $criteria = new Criteria(array_values(
            $products->map(fn(ProductEntity $product) => $product->getId())
        ));
        $criteria->addAssociations(['options', 'manufacturer', 'media']);

        return $this->salesChannelProductRepository->search($criteria, $context)
            ->getEntities();
    }

    private function getChildProducts(string $productId, SalesChannelContext $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('parentId', $productId));
        $criteria->addAssociations(['options', 'manufacturer', 'media', 'cover']);
        return $this->salesChannelProductRepository->search($criteria, $context);
    }
}