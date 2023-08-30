<?php

namespace devsi\Crudy\Services\Api;

use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Contracts\Foundation\Application;
use devsi\Crudy\Services\PathsAndNamespacesService;

/**
 * @property string $laravel
 */
class RemoveApiCrudService
{
    use InteractsWithIO;

    public PathsAndNamespacesService $pathsAndNamespacesService;
    public function __construct(
        PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
        Application $application
    )
    {
        $this->pathsAndNamespacesService = $pathsAndNamespacesService;
        $this->output = $consoleOutput;
        $this->laravel = $application->getNamespace();
    }

    public function pathsForFiles($namingConvention): array
    {
        return
        [
            'controller' => $this->pathsAndNamespacesService->getRealpathBaseCustomApiController($namingConvention),
            'request' => $this->pathsAndNamespacesService->getRealpathBaseCustomRequest($namingConvention),
            'model' => $this->pathsAndNamespacesService->getRealpathBaseCustomModel($namingConvention),
            'resource' => $this->pathsAndNamespacesService->getRealpathBaseCustomResource($namingConvention),
        ];
    }


}
