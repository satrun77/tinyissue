<?php

namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
use Codeception\Configuration;
use Codeception\Exception\ElementNotFound;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Tinyissue\Model;
use Tinyissue\Creatables;
use Tinyissue\Fetchables;

class FunctionalHelper extends \Codeception\Module
{
    use Creatables, Fetchables;

    /**
     * Get response content as Json object.
     *
     * @return \stdClass
     */
    public function getJsonResponseContent()
    {
        return json_decode($this->getResponseContent());
    }

    /**
     * Get response content.
     *
     * @return string
     *
     * @throws \Codeception\Exception\Module
     */
    public function getResponseContent()
    {
        return $this->getModule('Laravel5')->client->getInternalResponse()->getContent();
    }

    public function sendPostRequest(
        $action,
        array $actionParams,
        array $postParams,
        array $files = [],
        array $server = [],
        $content = null
    ) {
        $module = $this->getModule('Laravel5');
        $uri    = $module->getApplication()->url->action($action, $actionParams);
        $module->client->request('POST', $uri, $postParams, $files, $server, $content);
        $this->debugResponse();
    }

    protected function debugResponse()
    {
        $module = $this->getModule('Laravel5');
        $this->debugSection('Response', $module->client->getInternalResponse()->getStatus());
        $this->debugSection('Page', $module->client->getHistory()->current()->getUri());
        $this->debugSection('Cookies', $module->client->getInternalRequest()->getCookies());
        $this->debugSection('Headers', $module->client->getInternalResponse()->getHeaders());
    }

    public function submitFormWithFileToUri($selector, $uri, array $files, array $params = [])
    {
        $form = $this->matchForm($selector);

        // Upload files
        foreach ($files as $fieldName => $fileNames) {
            $this->uploadFileWithForm($form, $uri, $fieldName, $fileNames);
        }

        // Make sure upload token is same as upload files request
        $params['upload_token'] = $form->get('upload_token')->getValue();
        $form->setValues($params);

        $this->debugSection('Uri', $form->getUri());
        $this->debugSection($form->getMethod(), $form->getValues());

        // Save Form request
        $module = $this->getModule('Laravel5');
        $module->client->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), []);
        $this->debugResponse();
    }

    /**
     * @param string $selector
     *
     * @return Form
     */
    protected function matchForm($selector)
    {
        $form = $this->match($selector)->form();

        if (!$form instanceof Form) {
            throw new ElementNotFound($selector, 'Form');
        }

        return $form;
    }

    /**
     * @param string $selector
     *
     * @return Crawler
     */
    protected function match($selector)
    {
        return $this->getModule('Laravel5')->client->getCrawler()->filter($selector);
    }

    public function uploadFileWithForm($selectorOrForm, $uri, $fieldName, $fileNames)
    {
        // find form
        if (!$selectorOrForm instanceof Form) {
            $form = $this->matchForm($selectorOrForm);
        } else {
            $form = $selectorOrForm;
        }

        // Make sure fileNames is array
        if (!is_array($fileNames)) {
            $fileNames = [$fileNames];
        }

        /** @var $file \Symfony\Component\DomCrawler\Field\FileFormField */
        $file = $form->get($fieldName);

        // Upload files
        foreach ($fileNames as $fileName) {
            $filePath = Configuration::dataDir() . $fileName;
            if (!is_readable($filePath)) {
                $this->fail("file $filePath not found in Codeception data path. Only files stored in data path accepted");
            }

            // Attach file to form file
            $file->upload($filePath);

            $this->debugSection('Uri', $uri);
            $this->debugSection($form->getMethod(), $form->getValues());
            $this->debugSection('Files', $form->getPhpFiles());

            // Upload files request
            $module = $this->getModule('Laravel5');
            $module->client->request($form->getMethod(), $uri, $form->getPhpValues(), $form->getPhpFiles());
            $this->debugResponse();
        }
    }
}
