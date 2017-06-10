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

use Tinyissue\Model\ModelAbstract;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * Attachment is model class for project attachments.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int           $id
 * @property int           $uploaded_by
 * @property int           $issue_id
 * @property int           $comment_id
 * @property string        $filename
 * @property string        $fileextension
 * @property int           $filesize
 * @property string        $upload_token
 * @property Project\Issue $issue
 * @property User          $user
 * @property Comment       $comment
 *
 * @method $this byUser($userOrId)
 * @method $this forToken($token)
 * @method $this filename($name)
 * @method string getFilePath()
 * @method string getLastModified()
 * @method string getSize()
 * @method string getMimetype()
 * @method \Illuminate\Http\Response getDisplayResponse(\Illuminate\Http\Request $request)
 */
class Attachment extends ModelAbstract
{
    use AttachmentRelations, AttachmentScopes;

    /**
     * Timestamp enabled.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'projects_issues_attachments';

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    protected $fillable = [
        'uploaded_by',
        'filename',
        'fileextension',
        'filesize',
        'upload_token',
    ];

    /**
     * @param User|null $user
     *
     * @return \Tinyissue\Repository\Project\Issue\Attachment\Updater
     */
    public function updater(User $user = null)
    {
        return parent::updater($user);
    }

    /**
     * Whether or not the file extension is supported image type.
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
     * Url to attachment download.
     *
     * @return string
     */
    public function download()
    {
        return \URL::to('project/' . $this->issue->project_id . '/issue/' . $this->issue_id . '/download/' . $this->id);
    }

    /**
     * Url to display attachment.
     *
     * @return string
     */
    public function display()
    {
        return \URL::to('project/' . $this->issue->project_id . '/issue/' . $this->issue_id . '/display/' . $this->id);
    }

    /**
     * Generate a URL to delete attachment.
     *
     * @return string
     */
    public function toDelete()
    {
        return \URL::to('project/' . $this->issue->project_id . '/issue/' . $this->issue_id . '/delete/' . $this->id);
    }
}
