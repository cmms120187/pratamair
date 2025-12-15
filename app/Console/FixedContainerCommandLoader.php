<?php

namespace App\Console;

use Illuminate\Console\Application;
use Illuminate\Console\ContainerCommandLoader as BaseContainerCommandLoader;
use Symfony\Component\Console\Command\Command;

class FixedContainerCommandLoader extends BaseContainerCommandLoader
{
    /**
     * The Artisan application instance.
     *
     * @var \Illuminate\Console\Application
     */
    protected $artisan;

    /**
     * Create a new command loader instance.
     *
     * @param  \Psr\Container\ContainerInterface  $container
     * @param  array  $commandMap
     * @param  \Illuminate\Console\Application  $artisan
     * @return void
     */
    public function __construct($container, array $commandMap, Application $artisan)
    {
        parent::__construct($container, $commandMap);
        $this->artisan = $artisan;
    }

    /**
     * Resolve a command from the container.
     *
     * @param  string  $name
     * @return \Symfony\Component\Console\Command\Command
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function get(string $name): Command
    {
        $command = parent::get($name);

        // Ensure the command has the Laravel instance set
        if ($command instanceof \Illuminate\Console\Command) {
            $command->setLaravel($this->artisan->getLaravel());
        }

        return $command;
    }
}

