<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tinyissue\Model\Project\Issue;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Query;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * Attachment is model class for project attachments
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @property int           $id
 * @property int           $uploaded_by
 * @property int           $issue_id
 * @property int           $comment_id
 * @property string        $filename
 * @property string        $fileextension
 * @property int           $filesize
 * @property string        $upload_token
 * @property Project\Issue $issue
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Attachment extends BaseModel
{
    public $timestamps = true;
    protected $table = 'projects_issues_attachments';
    protected $fillable = [
        'uploaded_by',
        'filename',
        'fileextension',
        'filesize',
        'upload_token',
    ];

    /**
     * An attachment is belong to one issue  (inverse relationship of Project\Issue::attachments)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issue()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue', 'issue_id');
    }

    /**
     * An attachment has one user upladed to (inverse relationship of User::attachments).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'uploaded_by');
    }

    /**
     * An attachment can belong to a comment (inverse relationship of Comments::attachments).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo('Tinyissue\Model\Project\Issue\Comment', 'comment_id');
    }

    /**
     * Upload the attachment
     *
     * @param array   $input
     * @param Project $project
     * @param User    $user
     *
     * @return bool
     */
    public function upload(array $input, Project $project, User $user)
    {
        $relativePath = '/' . config('tinyissue.uploads_dir') . '/' . $project->id . '/' . $input['upload_token'];
        \Storage::disk('local')->makeDirectory($relativePath, 0777, true);
        $path = config('filesystems.disks.local.root') . $relativePath;

        /* @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
        $uploadedFile = $input['upload'];
        $file = $uploadedFile->move($path, $uploadedFile->getClientOriginalName());

        $this->uploaded_by = $user->id;
        $this->filename = $file->getFilename();
        $this->fileextension = $file->getExtension();
        $this->filesize = $file->getSize();
        $this->upload_token = $input['upload_token'];

        return $this->save();
    }

    /**
     * Remove a attachment that is pending from a issue/comment
     *
     * @param array   $input
     * @param Project $project
     * @param User    $user
     *
     * @return void
     */
    public function remove(array $input, Project $project, User $user)
    {
        $this->where('uploaded_by', '=', $user->id)
            ->where('upload_token', '=', $input['upload_token'])
            ->where('filename', '=', $input['filename'])
            ->delete();

        $path = config('filesystems.disks.local.root') . '/' . config('tinyissue.uploads_dir') . '/' . $project->id . '/' . $input['upload_token'];
        $this->deleteFile($path, $input['filename']);
    }

    /**
     * Delete the physical file of an attachment.
     *
     * @param string $path
     * @param string $filename
     */
    public function deleteFile($path, $filename)
    {
        @unlink($path . '/' . $filename);
        @rmdir($path);
    }

    /**
     * Whether or not the file extension is supported image type
     *
     * @return bool
     */
    public function isImage()
    {
        return in_array($this->fileextension, [
            'jpg',
            'jpeg',
            'JPG',
            'JPEG',
            'png',
            'PNG',
            'gif',
            'GIF',
        ]);
    }

    /**
     * Url to attachment download
     *
     * @return string
     */
    public function download()
    {
        return \URL::to('project/' . $this->issue->project_id . '/issue/' . $this->issue_id . '/download/' . $this->id);
    }

    /**
     * Url to display attachment
     *
     * @return string
     */
    public function display()
    {
        return \URL::to('project/' . $this->issue->project_id . '/issue/' . $this->issue_id . '/display/' . $this->id);
    }
}
