<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Tinyissue\Extensions\Auth\LoggedUser;
use Tinyissue\Form\ExportIssues;
use Tinyissue\Model\Project;

/**
 * ComposerServiceProvider is the view service provider binding data to specific views.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ComposerServiceProvider extends ServiceProvider
{
    use LoggedUser;

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Load variable into project side bar template
        \View::composer('layouts/sidebar/project', function (View $view) {
            $this->loadExportIssuesForm($view);

            $this->loadProjectSideBarDefaultVariables($view->project, $view);
        });
    }

    /**
     * Load any variables that are default to project side bar.
     *
     * @param Project $project
     * @param View    $view
     */
    protected function loadProjectSideBarDefaultVariables(Project $project, View $view)
    {
        if (!$view->offsetExists('closed_issues_count')) {
            $view->with('closed_issues_count', $project->countClosedIssues($this->getLoggedUser()));
        }

        if (!$view->offsetExists('open_issues_count')) {
            $view->with('open_issues_count', $project->countOpenIssues($this->getLoggedUser()));
        }

        if (!$view->offsetExists('project_users') || !$view->project_users instanceof Collection) {
            $view->with('project_users', $project->getUsers());
        }
    }

    /**
     * Add export form to project sidebar.
     *
     * @param View $view
     *
     * @return void
     */
    protected function loadExportIssuesForm(View $view)
    {
        $exportForm = new ExportIssues($this->app);
        $exportForm->setup(['project' => $view->project]);

        $view->with('exportForm', $exportForm);
    }

    /**
     * Register any application services.
     */
    public function register()
    {
    }
}
