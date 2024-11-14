<?php

declare(strict_types=1);

namespace SasVariantSwitch\Decorator;

use SasVariantSwitch\Services\ElementResolverHelper;
use SasVariantSwitch\Storefront\Page\ProductListingConfigurationLoader;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\CmsElementResolverInterface;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\Cms\CrossSellingCmsElementResolver;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: CrossSellingCmsElementResolver::class)]
class CrossSellingElementResolverDecorator extends AbstractCmsElementResolver
{
    public function __construct(
        #[AutowireDecorated]
        private readonly CmsElementResolverInterface $inner,
        private readonly ProductListingConfigurationLoader $listingConfigurationLoader,
        private readonly ElementResolverHelper $elementResolverHelper,
        private readonly EntityRepository $productRepository,
    ) {
    }

    public function getType(): string
    {
        return $this->inner->getType();
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return $this->inner->collect($slot, $resolverContext);
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $this->inner->enrich($slot, $resolverContext, $result);
        $context = $resolverContext->getSalesChannelContext();
        $data = $slot->getData();
        $crossSellings = $data?->getCrossSellings();

        if ($crossSellings === null) {
            return;
        }

        foreach ($crossSellings as $crossSelling) {
            $products = $crossSelling->getProducts();

            // need to reload products because the cross selling loader does not load media fields etc.
            $products = $this->reloadProducts($products, $context);
            $products = $this->elementResolverHelper->convertResolveVariantProducts($products, $context);
            $this->listingConfigurationLoader->loadListing($products, $context);
            $crossSelling->setProducts($products);
        }
        $data->setCrossSellings($crossSellings);
        $slot->setData($data);
    }

    private function reloadProducts(ProductCollection $products, SalesChannelContext $context): ProductCollection
    {
        if ($products->count() === 0) {
            return $products;
        }

        $criteria = new Criteria([$products->map(fn(ProductEntity $product) => $product->getId())]);
        $criteria->addAssociations(['options', 'manufacturer', 'media']);

        return $this->productRepository->search($criteria, $context->getContext())
            ->getEntities();
    }
}

