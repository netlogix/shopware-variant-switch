<?php declare(strict_types=1);

namespace SasVariantSwitch\Subscriber;

use SasVariantSwitch\SasVariantSwitch;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Search\SearchPageLoadedEvent;
use Shopware\Storefront\Page\Wishlist\WishlistPageLoadedEvent;
use Shopware\Storefront\Pagelet\Wishlist\GuestWishlistPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SasVariantSwitch\Storefront\Event\ProductBoxLoadedEvent;
use SasVariantSwitch\Storefront\Page\ProductListingConfigurationLoader;

class ProductListingResultLoadedSubscriber implements EventSubscriberInterface
{
    private ProductListingConfigurationLoader $listingConfigurationLoader;
    private SystemConfigService $systemConfigService;

    public function __construct(
        ProductListingConfigurationLoader $listingConfigurationLoader,
        SystemConfigService $systemConfigService
    ) {
        $this->listingConfigurationLoader = $listingConfigurationLoader;
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents()
    {
        return [
            // 'sales_channel.product.loaded' => 'handleProductListingLoadedRequest',
            ProductListingResultEvent::class => [
                ['handleProductListingLoadedRequest', 201],
            ],
            ProductBoxLoadedEvent::class => [
                ['handleProductBoxLoadedRequest', 201],
            ],
            GuestWishlistPageletLoadedEvent::class => [
                ['handleGuestWishlistPageLoadedEvent', 201],
            ],
            WishlistPageLoadedEvent::class => [
                ['handleWishlistPageLoadedEvent', 201],
            ],
            SearchPageLoadedEvent::class => [
                ['handleSearchPageLoadedEvent', 201],
            ],
        ];
    }

    public function handleProductListingLoadedRequest(ProductListingResultEvent $event): void
    {
        $context = $event->getSalesChannelContext();

        if (!$this->systemConfigService->getBool(SasVariantSwitch::SHOW_ON_PRODUCT_CARD, $context->getSalesChannelId())) {
            return;
        }

        /** @var ProductCollection $entities */
        $entities = $event->getResult()->getEntities();

        $this->listingConfigurationLoader->loadListing($entities, $context);
    }

    public function handleProductBoxLoadedRequest(ProductBoxLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();

        if (!$this->systemConfigService->getBool(SasVariantSwitch::SHOW_ON_PRODUCT_CARD, $context->getSalesChannelId())) {
            return;
        }

        $this->listingConfigurationLoader->loadListing(new ProductCollection([$event->getProduct()]), $context);
    }


    public function handleGuestWishlistPageLoadedEvent(GuestWishlistPageletLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();

        if (!$this->systemConfigService->getBool(SasVariantSwitch::SHOW_ON_WISHLIST_PAGE, $context->getSalesChannelId())) {
            return;
        }

        /* @var SalesChannelProductCollection $products */
        $products = $event->getPagelet()->getSearchResult()->getProducts();

        $this->listingConfigurationLoader->loadListing($products, $context);
    }

    public function handleWishlistPageLoadedEvent (WishlistPageLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();

        if (!$this->systemConfigService->getBool(SasVariantSwitch::SHOW_ON_WISHLIST_PAGE, $context->getSalesChannelId())) {
            return;
        }

        /* @var SalesChannelProductCollection $products */
        $products = $event->getPage()->getWishlist()->getProductListing()->getEntities();

        $this->listingConfigurationLoader->loadListing($products, $context);
    }

    public function handleSearchPageLoadedEvent(SearchPageLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();

        if (!$this->systemConfigService->getBool(SasVariantSwitch::SHOW_ON_SEARCH_RESULT_PAGE, $context->getSalesChannelId())) {
            return;
        }

        /* @var SalesChannelProductCollection $products */
        $products = $event->getPage()->getListing()->getEntities();

        $this->listingConfigurationLoader->loadListing($products, $context);
    }
}
