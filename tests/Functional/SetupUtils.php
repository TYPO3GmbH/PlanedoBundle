<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Message\UpdateFeed;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Tests\Functional\EntityManagerWrapper;
use Crell\Bundle\Planedo\Tests\TestApplication\Kernel;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

trait SetupUtils
{
    use EntityManagerWrapper;

    private ContainerInterface $container;

    public function initialize(array $config = []): ContainerInterface
    {
        $kernel = new Kernel($config);
        $kernel->boot();

        return $this->container = $kernel->getContainer();
    }

    /**
     * Get our actual test container.
     *
     * In order to have functional tests in a bundle, we need to custom override
     * the kernel, which means its own container.  Which means the container()
     * and getContainer() methods of KernelTestCase are no longer useful, so we
     * need a different name for the actual container that we're using.
     *
     * Symfony really hates Bundles.
     */
    protected function getRealContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function assertRawEntryCount(int $expected): void
    {
        $em = $this->entityManager();

        /** @var FeedEntryRepository $entryRepo */
        $entryRepo = $em->getRepository(FeedEntry::class);
        $entries = $entryRepo->findAll();
        self::assertCount($expected, $entries);
    }

    protected function populateFeeds(): void
    {
        $container = self::getContainer();

        /** @var MessageBusInterface $bus */
        $bus = $container->get(MessageBusInterface::class);

        $em = $this->entityManager();

        /** @var Feed[] $feeds */
        $feeds = $em->getRepository(Feed::class)->findAll();
        self::assertCount(3, $feeds);
        foreach ($feeds as $feed) {
            $bus->dispatch(new UpdateFeed($feed->getId()));
        }
    }

    // @todo Come back to this once the old tests aren't so broken.
    //abstract protected static function getContainer(): ContainerInterface;
}
