<?php

namespace App\Console;

use Illuminate\Console\Application;
use Illuminate\Console\ContainerCommandLoader;

class FixedArtisanApplication extends Application
{
    /**
     * Set the container command loader for lazy resolution.
     *
     * @return $this
     */
    public function setContainerCommandLoader()
    {
        $this->setCommandLoader(
            new FixedContainerCommandLoader($this->laravel, $this->commandMap, $this)
        );

        return $this;
    }
}

