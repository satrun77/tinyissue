<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project\Issue\Attachment;

use Illuminate\Database\Eloquent;
use Tinyissue\Model\User;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the Project\Issue\Attachment model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int                $id
 * @property int                $uploaded_by
 * @property int                $issue_id
 * @property int                $comment_id
 * @property string             $filename
 * @property string             $fileextension
 * @property int                $filesize
 * @property string             $upload_token
 * @property Project\Issue      $issue
 *
 * @method   Eloquent\Model     save()
 * @method   Eloquent\Model     delete()
 * @method   Eloquent\Model     where()
 */
trait CrudTrait
{
    /**
     * Upload the attachment
     *
     * @param array   $input
     * @param Project $project
     * @param User    $user
     *
     * @return Eloquent\Model
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
        $file = $path . '/' . $filename;
        if (file_exists($file)) {
            unlink($file);
        }

        if (is_dir($path)) {
            rmdir($path);
        }
    }
}
