<?php

namespace SasVariantSwitch\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductListingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ProductListingCriteriaEvent::class => 'onProductListingCriteria',
        ];
    }

    public function onProductListingCriteria(ProductListingCriteriaEvent $event): void
    {
        $event->getCriteria()
            ->addAssociation('media')
            ->getAssociation('children.media')
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING, true))
        ;
    }
}