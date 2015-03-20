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

use Illuminate\Http\Request;
use Tinyissue\Form\Tag as Form;
use Tinyissue\Http\Controllers\Controller;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Tag;

/**
 * TagsController is the controller class for managing administration request related to tags
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class TagsController extends Controller
{
    /**
     * Tag index page (List current tags)
     *
     * @param Tag $tag
     *
     * @return \Illuminate\View\View
     */
    public function getIndex(Tag $tag)
    {
        return view('administration.tags.index', [
            'tags'     => $tag->getGroupTags(),
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * Add new tag page
     *
     * @param Form $form
     *
     * @return \Illuminate\View\View
     */
    public function getNew(Form $form)
    {
        return view('administration.tags.new', [
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * To create new tag
     *
     * @param Tag             $tag
     * @param FormRequest\Tag $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNew(Tag $tag, FormRequest\Tag $request)
    {
        $tag->fill($request->all())->save();

        return redirect('administration/tags')->with('notice', trans('tinyissue.tag_added'));
    }

    /**
     * Edit an existing tag
     *
     * @param Tag  $tag
     * @param Form $form
     *
     * @return \Illuminate\View\View
     */
    public function getEdit(Tag $tag, Form $form)
    {
        return view('administration.tags.edit', [
            'tag'      => $tag,
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * To update tag details
     *
     * @param Tag             $tag
     * @param FormRequest\Tag $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(Tag $tag, FormRequest\Tag $request)
    {
        $tag->update($request->all());

        return redirect('administration/tags')->with('notice', trans('tinyissue.tag_updated'));
    }

    /**
     * Ajax: to search for tag by keyword (used by auto complete tag field)
     *
     * @param Tag     $tag
     * @param string  $term
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTags(Tag $tag, Request $request, $term = '')
    {
        $tags = [];
        $term = (string) $request->input('term', $term);
        if (!empty($term)) {
            $tags = $tag->searchTags($term)->filter(function (Tag $tag) {
                return !($tag->name == 'open' || $tag->name == 'closed');
            })->map(function (Tag $tag) {
                return [
                    'value' => $tag->id,
                    'label' => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toArray();
        }
        return response()->json($tags);
    }
}
