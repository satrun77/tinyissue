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

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;
use Tinyissue\Policies\AdminPolicy;
use Tinyissue\Policies\AttachmentPolicy;
use Tinyissue\Policies\CommentPolicy;
use Tinyissue\Policies\IssuePolicy;
use Tinyissue\Policies\NotePolicy;
use Tinyissue\Policies\ProjectPolicy;
use Tinyissue\Policies\TagPolicy;
use Tinyissue\Policies\UserPolicy;

/**
 * AuthServiceProvider.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Project::class                  => ProjectPolicy::class,
        User::class                     => UserPolicy::class,
        Tag::class                      => TagPolicy::class,
        Project\Issue::class            => IssuePolicy::class,
        Project\Note::class             => NotePolicy::class,
        Project\Issue\Comment::class    => CommentPolicy::class,
        Project\Issue\Attachment::class => AttachmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('manage-admin', AdminPolicy::class . '@manage');
        Gate::define('viewName', UserPolicy::class . '@viewName');
    }
}
