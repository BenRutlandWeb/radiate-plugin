<?php

namespace Radiate\Events\Console;

use Radiate\Console\GeneratorCommand;

class MakeListener extends GeneratorCommand
{
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Listener';

    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'make:listener {name : The name of the listener}
                                          {--force : Overwrite the listener if it exists}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Make a listener';

    /**
     * Get the stub path.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/listener.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace(string $rootNamespace)
    {
        return $rootNamespace . '\\Listeners';
    }
}
