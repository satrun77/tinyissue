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
use Tinyissue\Repository\Repository;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class Fetcher extends Repository
{
    protected $storage;

    public function __construct(Project\Issue\Attachment $model)
    {
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    protected function getStorage()
    {
        if (is_null($this->storage)) {
            $this->storage = \Storage::disk('local');
        }

        return $this->storage;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return
            config('tinyissue.uploads_dir') . '/' .
            $this->model->issue->project_id . '/' .
            $this->model->upload_token . '/' .
            $this->model->filename;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->getStorage()->size($this->getFilePath());
    }

    /**
     * @return string
     */
    public function getLastModified()
    {
        return $this->getStorage()->lastModified($this->getFilePath());
    }

    /**
     * @return string
     */
    public function getMimetype()
    {
        return $this->getStorage()->getDriver()->getMimetype($this->getFilePath());
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getDisplayResponse(Request $request)
    {
        $time    = $this->getLastModified();

        $response = new Response();
        $response->setEtag(md5($time . $this->getFilePath()));
        $response->setExpires(new \DateTime('@' . ($time + 60)));
        $response->setLastModified(new \DateTime('@' . $time));
        $response->setPublic();
        $response->setStatusCode(200);

        $response->header('Content-Type', $this->getMimetype());
        $response->header('Content-Length', $this->getSize());
        $response->header('Content-Disposition', 'inline; filename="' . $this->model->filename . '"');
        $response->header('Cache-Control', 'must-revalidate');

        if (!$response->isNotModified($request)) {
            // Return file if first request / modified
            $response->setContent($this->getStorage()->get($this->getFilePath()));
        }

        return $response;
    }
}
