<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Process\Listeners;

use Hyperf\Process\ProcessRegister;
use Hyperf\Contract\ProcessInterface;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;

/**
 * @Listener
 */
class BeforeMainServerStartListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        /** @var BeforeMainServerStart $event */
        $server = $event->server;
        $config = $event->serverConfig;
        $processes = $config['processes'] ?? [];

        // Retrieve the processes have been registered.
        $processes = array_merge($processes, ProcessRegister::all());
        foreach ($processes as $process) {
            if (is_string($process)) {
                $instance = $this->container->get($process);
            } else {
                $instance = $process;
            }
            if ($instance instanceof ProcessInterface) {
                $instance->bind($server);
            }
        }
    }
}
