<?php

namespace Knp\Component\Pager\Event\Subscriber\Paginate\Doctrine\ODM\MongoDB;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use Doctrine\ODM\MongoDB\Query\Builder;

class QueryBuilderSubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event)
    {
        if ($event->target instanceof Builder) {
            // get the count using a count() query
            $event->count = $event->target->count()->getQuery()->execute();

            // change target into query
            $event->target = $event->target->find()->getQuery();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 10 /*make sure to transform before any further modifications*/)
        );
    }
}
