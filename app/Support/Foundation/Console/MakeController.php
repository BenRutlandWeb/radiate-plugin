<?php

namespace Plugin\Support\Foundation\Console;

use Plugin\Support\Console\GeneratorCommand;

class MakeController extends GeneratorCommand
{
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'make:controller {name : The name of the controller}
                                            {--rest : Generate a REST controller}
                                            {--force : Overwrite the controller if it exists}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Make a controller';

    /**
     * Get the stub path.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('rest')) {
            return __DIR__ . '/stubs/controller-rest.stub';
        }

        return __DIR__ . '/stubs/controller-ajax.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace(string $rootNamespace)
    {
        return $rootNamespace . '\\Http\\Controllers';
    }
}
