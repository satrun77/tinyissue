<?php

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Model;

class   Tag extends Model
{
    protected $table      = 'tags';
    public    $timestamps = true;
    public    $fillable   = ['parent_id', 'name', 'bgcolor', 'group'];

    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    const GROUP_STATUS = 'status';

    public function parent()
    {
        return $this->belongsTo('Tinyissue\Model\Tag', 'parent_id');
    }

    public function tags()
    {
        return $this->hasMany('Tinyissue\Model\Tag', 'parent_id');
    }

    public function issues()
    {
        return $this->belongsToMany('Tinyissue\Model\Project\Issue', 'projects_issues_tags', 'issue_id', 'tag_id');
    }

    public function getGroupTags()
    {
        return $this->with('tags')->where('group', '=', true)->orderBy('group', 'DESC')->orderBy('name', 'ASC')->get();
    }

    public function getGroups()
    {
        return $this->where('group', '=', true)->orderBy('name', 'ASC')->get();
    }

    public function searchTags($term)
    {
        return $this->with('parent')->where('name', 'like', '%' . $term . '%')->where('group', '=', false)->get();
    }

    public function to($url)
    {
        return \URL::to('administration/tag/' . $this->id . (($url) ? '/' . $url : ''));
    }

    public function getFullNameAttribute()
    {
        return (isset($this->parent->name)? ucwords($this->parent->name) : '') . ':' . ucwords($this->attributes['name']);
    }
}
