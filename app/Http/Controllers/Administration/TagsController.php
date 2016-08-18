<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Controllers\Administration;

use Tinyissue\Form\Tag as Form;
use Tinyissue\Http\Controllers\Controller;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Tag;

/**
 * TagsController is the controller class for managing administration request related to tags.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class TagsController extends Controller
{
    /**
     * Tag index page (List current tags).
     *
     * @param Tag $tag
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(Tag $tag)
    {
        return view('administration.tags.index', [
            'tags'     => $tag->getGroupWithTags(),
            'projects' => $this->getLoggedUser()->getProjects(),
        ]);
    }

    /**
     * Add new tag page.
     *
     * @param Form $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getNew(Form $form)
    {
        return view('administration.tags.new', [
            'form'     => $form,
            'projects' => $this->getLoggedUser()->getProjects(),
        ]);
    }

    /**
     * To create new tag.
     *
     * @param Tag             $tag
     * @param FormRequest\Tag $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNew(Tag $tag, FormRequest\Tag $request)
    {
        $tag->updater()->create($request->all());

        return redirect('administration/tags')->with('notice', trans('tinyissue.tag_added'));
    }

    /**
     * Edit an existing tag.
     *
     * @param Tag  $tag
     * @param Form $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getEdit(Tag $tag, Form $form)
    {
        return view('administration.tags.edit', [
            'tag'      => $tag,
            'form'     => $form,
            'projects' => $this->getLoggedUser()->getProjects(),
        ]);
    }

    /**
     * To update tag details.
     *
     * @param Tag             $tag
     * @param FormRequest\Tag $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(Tag $tag, FormRequest\Tag $request)
    {
        $tag->updater()->update($request->all());

        return redirect('administration/tags')->with('notice', trans('tinyissue.tag_updated'));
    }

    /**
     * Delete tag.
     *
     * @param Tag $tag
     *
     * @return mixed
     */
    public function getDelete(Tag $tag)
    {
        $tag->updater()->delete();

        return redirect('administration/tags')
            ->with('notice', trans('tinyissue.tag_has_been_deleted'));
    }
}
