<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project\Issue\Attachment;

use Tinyissue\Model\Project;
use Tinyissue\Model\User;
use Tinyissue\Repository\RepositoryUpdater;

class Updater extends RepositoryUpdater
{
    /**
     * @var Project\Issue\Attachment
     */
    protected $model;

    public function __construct(Project\Issue\Attachment $model)
    {
        $this->model = $model;
    }

    public function delete()
    {
        $path = $this->getUploadStorage($this->model->issue->project, $this->model->upload_token);
        $this->deleteFile($path, $this->model->filename);

        return parent::delete();
    }

    /**
     * Upload the attachment.
     *
     * @param array   $input
     * @param Project $project
     * @param User    $user
     *
     * @return Project\Issue\Attachment
     */
    public function upload(array $input, Project $project, User $user)
    {
        // Upload path
        $path = $this->getUploadStorage($project, $input['upload_token']);

        /* @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
        $uploadedFile = $input['upload'];
        $file         = $uploadedFile->move($path, $uploadedFile->getClientOriginalName());

        $this->model->uploaded_by   = $user->id;
        $this->model->filename      = $file->getFilename();
        $this->model->fileextension = $file->getExtension();
        $this->model->filesize      = $file->getSize();
        $this->model->upload_token  = $input['upload_token'];

        return $this->save();
    }

    /**
     * Remove a attachment that is pending from a issue/comment.
     *
     * @param array   $input
     * @param Project $project
     * @param User    $user
     *
     * @return void
     */
    public function remove(array $input, Project $project, User $user)
    {
        $this->model->byUser($user)->forToken($input['upload_token'])->filename($input['filename'])->delete();

        $path = $this->getUploadStorage($project, $input['upload_token']);
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
        $file = $path . '/' . $filename;

        if (file_exists($file)) {
            unlink($file);
        }

        if (is_dir($path)) {
            rmdir($path);
        }
    }

    /**
     * Update upload token for issue attachment.
     *
     * @param string  $token
     * @param int|User $uploadBy
     * @param int     $issueId
     *
     * @return Project\Issue\Attachment
     */
    public function updateIssueToken($token, $uploadBy, $issueId)
    {
        return $this->model->forToken($token)->byUser($uploadBy)->update(['issue_id' => $issueId]);
    }

    /**
     * Update upload token for comment attachment.
     *
     * @param string   $token
     * @param int|User $uploadBy
     * @param int      $issueId
     * @param int      $commentId
     *
     * @return Project\Issue\Attachment
     */
    public function updateCommentToken($token, $uploadBy, $issueId, $commentId)
    {
        if (!empty($token)) {
            return $this->model->forToken($token)->byUser($uploadBy)->update([
                'issue_id'   => $issueId,
                'comment_id' => $commentId,
            ]);
        }
    }
}
