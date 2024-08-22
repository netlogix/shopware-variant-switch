<?php

namespace SasVariantSwitch\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Storefront\Page\Wishlist\WishListPageProductCriteriaEvent;
use Shopware\Storefront\Pagelet\Wishlist\GuestWishListPageletProductCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductListingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ProductListingCriteriaEvent::class => 'onProductListingCriteria',
            WishListPageProductCriteriaEvent::class => 'onProductListingCriteria',
            GuestWishListPageletProductCriteriaEvent::class => 'onProductListingCriteria',
        ];
    }

    public function onProductListingCriteria(ProductListingCriteriaEvent|WishListPageProductCriteriaEvent|GuestWishListPageletProductCriteriaEvent $event): void
    {
        $event->getCriteria()
            ->addAssociation('media')
            ->getAssociation('children.media')
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING, true))
        ;
    }
}