<?php

namespace App\EventSubscriber;

use App\Event\AfterBookReturnEvent;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \App\Event\AfterBookQuantityAddEvent;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class BookUpdateSubscriber implements EventSubscriberInterface
{
    private HubInterface $hub;
    private EntityManagerInterface $entityManager;

    public function __construct(HubInterface $hub,EntityManagerInterface $entityManager)
    {
        $this->hub = $hub;
        $this->entityManager = $entityManager;
    }

    public function onAfterBookQuantityAddEvent(AfterBookQuantityAddEvent $event)
    {
        $book = $event->getBook();
        $originalQuantity = $event->getOriginalQuantity();
        $addBookCount = $book->getQuantity() - $originalQuantity;
        if ($addBookCount>0 && $book->getSubscribes()[0] )
        {
            $count = 0;
            for ($i=0;$i<count($book->getSubscribes(),0);$i++)
            {
                if ($book->getSubscribes()[$i]->getStatus() == 'noSent')
                {
                    $book->getSubscribes()[$i]->setStatus('sent');
                    $sentAt = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
                    $book->getSubscribes()[$i]->setSentAt($sentAt);

                    $this->entityManager->flush();

                    $updateSubscribes = new Update(
                        'https://library.com/subscribes',
                        json_encode([
                            'type'=>'subscribe',
                            'userId'=>$book->getSubscribes()[$i]->getNormalUser()->getId(),
                            'subscribeId'=>$book->getSubscribes()[$i]->getId(),
                            'status'=>$book->getSubscribes()[$i]->getStatus()
                        ])
                    );
                    $this->hub->publish($updateSubscribes);
                    $count ++;
                }
                if ($count >= $addBookCount)
                {
                    break;
                }
            }
        }

        if ($addBookCount<0 && $book->getSubscribes()[0] )
        {
            $count = 0;
            for ($i=count($book->getSubscribes())-1;$i>=0;$i--)
            {
                if ($book->getSubscribes()[$i]->getStatus() == 'sent')
                {
                    $book->getSubscribes()[$i]->setStatus('noSent');
                    $book->getSubscribes()[$i]->setSentAt(null);

                    $this->entityManager->flush();

                    $updateSubscribes = new Update(
                        'https://library.com/subscribes',
                        json_encode([
                            'type'=>'subscribe',
                            'userId'=>$book->getSubscribes()[$i]->getNormalUser()->getId(),
                            'subscribeId'=>$book->getSubscribes()[$i]->getId(),
                            'status'=>$book->getSubscribes()[$i]->getStatus()
                        ])
                    );
                    $this->hub->publish($updateSubscribes);
                    $count ++;
                }
                if ($count >= abs($addBookCount))
                {
                    break;
                }
            }
        }
    }

    public function onAfterBookReturnEvent(AfterBookReturnEvent $event)
    {
        $book = $event->getBook();
        $subscribes = $book->getSubscribes();
        if ($subscribes[0])
        {
            for ($i=0;$i<count($subscribes,0);$i++)
            {
                if ($subscribes[$i]->getStatus() == 'noSent')
                {
                    $subscribes[$i]->setStatus('sent');
                    $sentAt = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
                    $subscribes[$i]->setSentAt($sentAt);

                    $this->entityManager->flush();

                    $updateSubscribes = new Update(
                        'https://library.com/subscribes',
                        json_encode([
                            'type'=>'subscribe',
                            'userId'=>$subscribes[$i]->getNormalUser()->getId(),
                            'subscribeId'=>$subscribes[$i]->getId(),
                            'status'=>$subscribes[$i]->getStatus()
                        ])
                    );
                    $this->hub->publish($updateSubscribes);
                    break;
                }
            }

        }
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterBookQuantityAddEvent::class => 'onAfterBookQuantityAddEvent',
            AfterBookReturnEvent::class => 'onAfterBookReturnEvent'
        ];
    }
}
