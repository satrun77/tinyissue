<?php

namespace Tinyissue\Http\Controllers\Administration;

use Illuminate\Http\Request;
use Tinyissue\Form\Tag as Form;
use Tinyissue\Http\Controllers\Controller;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Tag;

class TagsController extends Controller
{
    public function getIndex(Tag $tag)
    {
        return view('administration.tags.index', [
            'tags'     => $tag->getGroupTags(),
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    public function getNew(Form $form)
    {
        return view('administration.tags.new', [
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    public function postNew(Tag $tag, FormRequest\Tag $request)
    {
        $tag->fill($request->all())->save();

        return redirect('administration/tags')->with('notice', trans('tinyissue.tag_added'));
    }

    public function getEdit(Tag $tag, Form $form)
    {
        return view('administration.tags.edit', [
            'tag'      => $tag,
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    public function postEdit(Tag $tag, FormRequest\Tag $request)
    {
        $tag->update($request->all());

        return redirect('administration/tags')->with('notice', trans('tinyissue.tag_updated'));
    }

    public function getTags(Tag $tag, Request $request)
    {
        $tags = [];
        $term = $request->input('term');
        if (!empty($term)) {
            $tags = $tag->searchTags($term)->filter(function($tag) {
                return !($tag->name == 'open' || $tag->name == 'closed');
            })->map(function($tag) {
                return [
                    'value' => $tag->id,
                    'label' => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            });
        }
        return response()->json($tags);
    }
}
