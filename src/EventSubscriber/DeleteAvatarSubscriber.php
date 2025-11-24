<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeleteAvatarSubscriber implements EventSubscriberInterface
{
    public function onDoctrine($event): void
    {
        // ...
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Doctrine' => 'onDoctrine',
        ];
    }
}
