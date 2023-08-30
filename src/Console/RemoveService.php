<?php

namespace devsi\Crudy\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use devsi\Crudy\Services\MakeGlobalService;
use devsi\Crudy\Services\PathsAndNamespacesService;
use devsi\Crudy\Services\RemoveCommentableService;

class RemoveService extends Command
{
    public MakeGlobalService $makeGlobalService;
    public PathsAndNamespacesService $pathsAndNamespacesService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rm:service {service_name} {--force}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a service';

    public function __construct(
        MakeGlobalService         $makeGlobalService,
        PathsAndNamespacesService $pathsAndNamespacesService
    )
    {
        parent::__construct();
        $this->makeGlobalService = $makeGlobalService;
        $this->pathsAndNamespacesService = $pathsAndNamespacesService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // we create our variables to respect the naming conventions
        $serviceName = ucfirst($this->argument('service_name'));
        $namingConvention = $this->makeGlobalService->getCommentableNamingConvention($serviceName);
        $force = $this->option('force');

        $completePath = $this->pathsAndNamespacesService->getRealpathBaseCustomService($namingConvention);

        if (File::exists($completePath)) {
            if ($force || $this->confirm('Do you want to delete ' . $completePath . '?')) {
                if (File::delete($completePath))
                    $this->line("<info>" . $completePath . " deleted</info>");
            }
        }
    }
}
