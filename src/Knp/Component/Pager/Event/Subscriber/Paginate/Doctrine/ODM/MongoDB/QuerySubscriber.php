<?php

namespace Knp\Component\Pager\Event\Subscriber\Paginate\Doctrine\ODM\MongoDB;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use Doctrine\ODM\MongoDB\Query\Query;

class QuerySubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event)
    {
        if ($event->target instanceof Query) {
            // only recalculate the count if it hasn't already been set in a previous subscriber
            if (null === $event->count) {
                // count
                $event->count = $event->target->count();
            }
            // items
            $type = $event->target->getType();
            if ($type !== Query::TYPE_FIND) {
                throw new \UnexpectedValueException('ODM query must be a FIND type query');
            }
            static $reflectionProperty;
            if (is_null($reflectionProperty)) {
                $reflectionClass = new \ReflectionClass('Doctrine\MongoDB\Query\Query');
                $reflectionProperty = $reflectionClass->getProperty('query');
                $reflectionProperty->setAccessible(true);
            }
            $queryOptions = $reflectionProperty->getValue($event->target);

            $queryOptions['limit'] = $event->getLimit();
            $queryOptions['skip'] = $event->getOffset();

            $resultQuery = clone $event->target;
            $reflectionProperty->setValue($resultQuery, $queryOptions);
            $cursor = $resultQuery->execute();

            $event->items = $cursor->toArray();
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 0)
        );
    }
}
