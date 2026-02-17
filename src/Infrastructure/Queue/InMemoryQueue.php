<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Infrastructure\Queue;

use Edge\NotificationCleanArch\Application\Port\QueuePort;

final class InMemoryQueue implements QueuePort
{
    /** @var list<callable(): void> */
    private array $jobs = [];

    public function dispatch(callable $job): void
    {
        $this->jobs[] = $job;
    }

    public function runAll(): void
    {
        while ($this->jobs !== []) {
            $job = array_shift($this->jobs);
            $job();
        }
    }

    public function count(): int
    {
        return count($this->jobs);
    }
}

