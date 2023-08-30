<?php

namespace devsi\Crudy\Services\Api;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\File;
use devsi\Crudy\Services\MakeGlobalService;
use devsi\Crudy\Services\PathsAndNamespacesService;
use Symfony\Component\Console\Output\ConsoleOutput;

class MakeApiRequestService
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


    public function replaceContentApiRequestStub($namingConvention, $laravelNamespace): array|string
    {
        $requestStub = File::get($this->pathsAndNamespacesService->getApiRequestStubPath());
        $requestStub = str_replace('DummyNamespace', $this->pathsAndNamespacesService->getDefaultNamespaceRequest($laravelNamespace), $requestStub);
        $requestStub = str_replace('DummyRootNamespace', $laravelNamespace, $requestStub);
        return str_replace('DummyClass', $namingConvention['singular_name'].'Request', $requestStub);
    }

    public function findAndReplaceApiRequestPlaceholderColumns($columns, $requestStub): array|string
    {
        $rules='';

        // we create our placeholders regarding columns
        foreach ($columns as $column)
        {
            $type     = explode(':', trim($column));
            $column   = $type[0];

            // our placeholders
            $rules .= str_repeat("\t", 3)."'".trim($column)."' => '"."required',\n";
        }

        $rules = $this->makeGlobalService->cleanLastLineBreak($rules);

        // we replace our placeholders
        return str_replace('DummyRulesRequest', $rules, $requestStub);
    }

    public function createApiRequestFile($requestStub, $namingConvention): void
    {
        if(!File::exists($this->pathsAndNamespacesService->getRealpathBaseRequest()))
            File::makeDirectory($this->pathsAndNamespacesService->getRealpathBaseRequest());

        // if the Request file doesn't exist, we create it
        if(!File::exists($this->pathsAndNamespacesService->getRealpathBaseCustomRequest($namingConvention)))
        {
            File::put($this->pathsAndNamespacesService->getRealpathBaseCustomRequest($namingConvention), $requestStub);
            $this->line("<info>Created Request:</info> ".$namingConvention['singular_name']);
        }
        else
            $this->error('Request ' .$namingConvention['singular_name']. ' already exists');
    }

    public function makeCompleteApiRequestFile($namingConvention, $columns, $laravelNamespace): void
    {
        $requestStub = $this->replaceContentApiRequestStub($namingConvention, $laravelNamespace);
        $requestStub = $this->findAndReplaceApiRequestPlaceholderColumns($columns, $requestStub);

        $this->createApiRequestFile($requestStub, $namingConvention);
    }
}
