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
use Shopware\Core\Content\Product\Cms\ProductSliderCmsElementResolver;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: ProductSliderCmsElementResolver::class)]
class ProductSliderElementResolverDecorator extends AbstractCmsElementResolver
{
    public function __construct(
        #[AutowireDecorated]
        private readonly CmsElementResolverInterface $inner,
        private readonly ProductListingConfigurationLoader $listingConfigurationLoader,
        #[Autowire(service: ElementResolverHelper::class)]
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
        $products = $data?->getProducts();

        $products = $this->elementResolverHelper->convertResolveVariantProducts($products, $context);
        $this->listingConfigurationLoader->loadListing($products, $context);

        $data->setProducts($products);
        $slot->setData($data);
    }
}

