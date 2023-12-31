<?php

namespace devsi\Crudy\Services\Commentable;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\File;
use devsi\Crudy\Services\MakeGlobalService;
use devsi\Crudy\Services\PathsAndNamespacesService;
use Symfony\Component\Console\Output\ConsoleOutput;

class MakeCommentableRequestService
{
    use InteractsWithIO;

    public PathsAndNamespacesService $pathsAndNamespacesService;
    public MakeGlobalService $makeGlobalService;
    public function __construct(
        PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
        MakeGlobalService $makeGlobalService
    )
    {
        $this->pathsAndNamespacesService = $pathsAndNamespacesService;
        $this->output = $consoleOutput;
        $this->makeGlobalService = $makeGlobalService;
    }


    public function replaceContentCommentableRequestStub($namingConvention, $laravelNamespace): array|string
    {
        $requestStub = File::get($this->pathsAndNamespacesService->getCommentableRequestStubPath());
        $requestStub = str_replace('DummyNamespace', $this->pathsAndNamespacesService->getDefaultNamespaceRequest($laravelNamespace), $requestStub);
        $requestStub = str_replace('DummyRootNamespace', $laravelNamespace, $requestStub);
        return str_replace('DummyClass', $namingConvention['model_name'].'Request', $requestStub);
    }

    public function createCommentableRequestFile($requestStub, $namingConvention): void
    {
        if(!File::exists($this->pathsAndNamespacesService->getRealpathBaseRequest()))
            File::makeDirectory($this->pathsAndNamespacesService->getRealpathBaseRequest());

        // if the Request file doesn't exist, we create it
        if(!File::exists($this->pathsAndNamespacesService->getRealpathBaseCustomCommentableRequest($namingConvention)))
        {
            File::put($this->pathsAndNamespacesService->getRealpathBaseCustomCommentableRequest($namingConvention), $requestStub);
            $this->line("<info>Created Request:</info> ".$namingConvention['model_name']);
        }
        else
            $this->error('Request ' .$namingConvention['model_name']. ' already exists');
    }

    public function makeCommentableCompleteRequestFile($namingConvention, $laravelNamespace): void
    {
        $requestStub = $this->replaceContentCommentableRequestStub($namingConvention, $laravelNamespace);

        $this->createCommentableRequestFile($requestStub, $namingConvention);
    }
}
