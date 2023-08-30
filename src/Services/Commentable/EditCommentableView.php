<?php

namespace devsi\Crudy\Services\Commentable;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\File;
use devsi\Crudy\Services\MakeGlobalService;
use devsi\Crudy\Services\PathsAndNamespacesService;
use Symfony\Component\Console\Output\ConsoleOutput;

class EditCommentableView
{
    use InteractsWithIO;

    public PathsAndNamespacesService $pathsAndNamespacesService;
    public MakeGlobalService $makeGlobalService;
    public string $placeholder = "{{comment_here}}";

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


    public function replaceContentCommentableViewStub($namingConvention, $nameParentOtherModel): array|string
    {
        $commentBlockStub = File::get($this->pathsAndNamespacesService->getCommentableCommentBlockPath());
        $commentBlockStub = str_replace('{{route_comment}}', $namingConvention['plural_low_variable_name'], $commentBlockStub);
        $commentBlockStub = str_replace('{{parent_variable}}', '$'.$nameParentOtherModel, $commentBlockStub);
        $commentBlockStub = str_replace('{{comment_variable}}', '$'.$namingConvention['singular_low_variable_name'], $commentBlockStub);
        return str_replace('{{name_relationship}}', $namingConvention['plural_low_variable_name'] ,$commentBlockStub);
    }

    public function fillViewWithForm($viewPath, $commentBlockStub): void
    {
        $contentView = File::get($viewPath);
        File::put($viewPath, str_replace($this->placeholder, $commentBlockStub, $contentView));
        $this->line("<info>Modified view:</info> ".$viewPath);
    }

    public function editViewFile($viewPath, $namingConvention, $nameParentOtherModel): void
    {
        $commentBlockStub = $this->replaceContentCommentableViewStub($namingConvention, $nameParentOtherModel);
        $this->fillViewWithForm($viewPath, $commentBlockStub);
    }
}
