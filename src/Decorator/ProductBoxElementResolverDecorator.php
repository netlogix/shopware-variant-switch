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
use Shopware\Core\Content\Product\Cms\ProductBoxCmsElementResolver;
use Shopware\Core\Content\Product\ProductCollection;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: ProductBoxCmsElementResolver::class)]
class ProductBoxElementResolverDecorator extends AbstractCmsElementResolver
{
    public function __construct(
        #[AutowireDecorated]
        private readonly CmsElementResolverInterface $inner,
        private readonly ProductListingConfigurationLoader $listingConfigurationLoader,
        private readonly ElementResolverHelper $elementResolverHelper,
    ) {
    }

    public function getType(): string
    {
        return $this->inner->getType();
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return $this->elementResolverHelper->addAssociationsToCriteriaList(
            $this->inner->collect($slot, $resolverContext)
        );
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $this->inner->enrich($slot, $resolverContext, $result);
        $context = $resolverContext->getSalesChannelContext();
        $data = $slot->getData();
        $product = $data?->getProduct();

        if ($product === null) {
            return;
        }

        $products = $this->elementResolverHelper->convertResolveVariantProducts(new ProductCollection([$product]), $context);
        $this->listingConfigurationLoader->loadListing($products, $context);
        $data->setProduct($products->first());
        $slot->setData($data);
    }
}

