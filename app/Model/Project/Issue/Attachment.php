<?php

namespace Tinyissue\Model\Project\Issue;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Model\Project;

class Attachment extends Model
{
    public $timestamps = true;
    protected $table = 'projects_issues_attachments';
    protected $fillable = array(
        'uploaded_by',
        'filename',
        'fileextension',
        'filesize',
        'upload_token',
    );

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
     * Upload the attachment.
     *
     * @param array $input
     *
     * @return bool
     */
    public function upload($input, Project $project, $userId)
    {
        $relativePath = '/uploads/'.$project->id.'/'.$input['upload_token'];
        \Storage::disk('local')->makeDirectory($relativePath, 0777, true);
        $path = config('filesystems.disks.local.root').$relativePath;

        /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $uploadedFile = $input['Filedata'];
        $file = $uploadedFile->move($path, $input['Filename']);

        $fill = array(
            'uploaded_by' => $userId,
            'filename' => $file->getFilename(),
            'fileextension' => $file->getExtension(),
            'filesize' => $file->getSize(),
            'upload_token' => $input['upload_token'],
        );

        $this->fill($fill);

        return $this->save();
    }

    /**
     * Remove a attachment that is pending from a issue/comment.
     *
     * @param array $input
     */
    public function remove($input, $project, $userId)
    {
        $this->where('uploaded_by', '=', $userId)
            ->where('upload_token', '=', $input['upload_token'])
            ->where('filename', '=', $input['filename'])
            ->delete();

        $path = config('filesystems.disks.local.root').'/uploads/'.$project->id.'/'.$input['upload_token'];
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
        @unlink($path.'/'.$filename);
        @rmdir($path);
    }

    public function isImage()
    {
        return in_array($this->fileextension, array(
            'jpg', 'jpeg', 'JPG', 'JPEG',
            'png', 'PNG',
            'gif', 'GIF',
        ));
    }

    public function download()
    {
        return \URL::to('project/'.$this->issue->project_id.'/issue/'.$this->issue_id.'/download/'.$this->id);
    }

    public function display()
    {
        return \URL::to('project/'.$this->issue->project_id.'/issue/'.$this->issue_id.'/display/'.$this->id);
    }
}
