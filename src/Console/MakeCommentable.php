<?php

namespace devsi\Crudy\Console;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use devsi\Crudy\Exceptions\ConsoleException;
use devsi\Crudy\Services\Commentable\EditCommentableView;
use devsi\Crudy\Services\Commentable\MakeCommentableRequestService;
use devsi\Crudy\Services\Commentable\MakeCommentableControllerService;
use devsi\Crudy\Services\MakeGlobalService;
use devsi\Crudy\Services\MakeMigrationService;
use devsi\Crudy\Services\MakeModelService;
use devsi\Crudy\Services\PathsAndNamespacesService;

class MakeCommentable extends Command
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public MakeCommentableControllerService $makeCommentableControllerService;
    public MakeCommentableRequestService $makeCommentableRequestService;
    public MakeMigrationService $makeMigrationService;
    public MakeModelService $makeModelService;
    public EditCommentableView $editCommentableView;
    public MakeGlobalService $makeGlobalService;
    public PathsAndNamespacesService $pathsAndNamespacesService;
    public string $nameParentModel = "";
    /**
     * @var string
     */
    public string $pathViewCommentable = "";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:commentable {commentable_name}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add commentable section to an existing view';

    public function __construct(
        MakeCommentableControllerService $makeCommentableControllerService,
        MakeCommentableRequestService    $makeCommentableRequestService,
        MakeMigrationService             $makeMigrationService,
        MakeModelService                 $makeModelService,
        EditCommentableView              $editCommentableView,
        MakeGlobalService                $makeGlobalService,
        PathsAndNamespacesService        $pathsAndNamespacesService,
    )
    {
        parent::__construct();
        $this->makeCommentableControllerService = $makeCommentableControllerService;
        $this->makeCommentableRequestService = $makeCommentableRequestService;
        $this->makeMigrationService = $makeMigrationService;
        $this->makeModelService = $makeModelService;
        $this->editCommentableView = $editCommentableView;
        $this->makeGlobalService = $makeGlobalService;
        $this->pathsAndNamespacesService = $pathsAndNamespacesService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws ConsoleException
     */
    public function handle(): void
    {
        // we create our variables to respect the naming conventions
        $commentableName = ucfirst($this->argument('commentable_name'));
        $namingConvention = $this->makeGlobalService->getCommentableNamingConvention($commentableName);
        $laravelNamespace = $this->laravel->getNamespace();


        /* *************************************************************************

                                        REQUEST

        ************************************************************************* */

        $this->makeCommentableRequestService->makeCommentableCompleteRequestFile($namingConvention, $laravelNamespace);

        /* *************************************************************************

                                        MODEL

        ************************************************************************* */

        if (!File::exists($this->pathsAndNamespacesService->getRealpathBaseModel()))
            File::makeDirectory($this->pathsAndNamespacesService->getRealpathBaseModel());

        // we create our model
        $this->setNameModelRelationship($namingConvention);

        /* *************************************************************************

                                     CONTROLLER

        ************************************************************************* */

        $namingConventionParent = $this->makeGlobalService->getCommentableParentModelConvention($this->nameParentModel);
        $this->makeCommentableControllerService->makeCompleteCommentableControllerFile($namingConvention, $laravelNamespace, $namingConventionParent['singular_low_variable_name']);


        /* *************************************************************************

                                        MIGRATION

        ************************************************************************* */

        $columns = ['comment:text'];
        if ($this->nameParentModel !== "")
            $columns[] = $this->nameParentModel . "_id:integer";
        $this->makeMigrationService->makeCompleteMigrationFile($namingConvention, $columns);

        /* *************************************************************************

                                        VIEW

        ************************************************************************* */
        $this->askChangeView($namingConvention);
    }

    /**
     * @throws ConsoleException
     */
    private function setNameModelRelationship($namingConvention): void
    {
        $type = "belongsTo";
        $infos = [];
        $singularName = $namingConvention['model_name'];
        $nameOtherModel = $this->ask('What is the name of the other model to which you want to add a commentable section? ex:Post');

        if ($nameOtherModel === null)
            throw new ConsoleException('Please provide a model name');

        $this->nameParentModel = $nameOtherModel;

        $correctNameOtherModel = ucfirst(Str::singular($nameOtherModel));
        $correctNameOtherModelWithNamespace = $this->laravel->getNamespace() . 'Models\\' . $correctNameOtherModel;
        if ($this->confirm('Do you confirm the creation of this relationship? "' . '$this->' . $type . '(\'' . $correctNameOtherModelWithNamespace . '\')"')) {
            $infos[] = ['name' => $nameOtherModel, 'type' => $type];
            $this->makeModelService->makeCompleteModelFile($infos, $singularName, $namingConvention, $this->laravel->getNamespace());
        } else
            $this->setNameModelRelationship($namingConvention);
    }

    private function askChangeView($namingConvention): void
    {
        $allViews = $this->makeGlobalService->getAllViewsFiles();
        $this->error("Before continuing, please indicate the placeholder as follows: {{comment_here}}, where you want the form to be displayed.");
        $chosenView = $this->choice(
            'On which view do you want to add the comment section?',
            $allViews,
        );
        $this->editCommentableView->editViewFile($chosenView, $namingConvention, $this->nameParentModel);
    }
}
