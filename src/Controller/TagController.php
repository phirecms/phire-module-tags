<?php
/**
 * Phire Tags Module
 *
 * @link       https://github.com/phirecms/phire-tags
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Tags\Controller;

use Phire\Tags\Model;
use Phire\Tags\Form;
use Phire\Tags\Table;
use Phire\Controller\AbstractController;
use Pop\Paginator\Paginator;

/**
 * Tag Controller class
 *
 * @category   Phire\Tags
 * @package    Phire\Tags
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class TagController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $tag = new Model\Tag();

        if ($tag->hasPages($this->config->pagination)) {
            $limit = $this->config->pagination;
            $pages = new Paginator($tag->getCount(), $limit);
            $pages->useInput(true);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('tags/index.phtml');
        $this->view->title  = 'Tags';
        $this->view->pages  = $pages;
        $this->view->tags   = $tag->getAll(
            $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
        );

        $this->send();
    }

    /**
     * Add action method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('tags/add.phtml');
        $this->view->title = 'Tags : Add';

        $this->view->form = new Form\Tag($this->application->config()['forms']['Phire\Tags\Form\Tag']);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $tag = new Model\Tag();
                $tag->save($this->view->form->getFields());
                $this->view->id = $tag->id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/tags/edit/'. $tag->id);
            }
        }

        $this->send();
    }

    /**
     * Edit action method
     *
     * @param  int $id
     * @return void
     */
    public function edit($id)
    {
        $tag = new Model\Tag();
        $tag->getById($id);

        if (!isset($tag->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/tags');
        }

        $this->prepareView('tags/edit.phtml');
        $this->view->title     = 'Tags';
        $this->view->tag_title = $tag->title;

        $fields = $this->application->config()['forms']['Phire\Tags\Form\Tag'];
        $fields[1]['title']['attributes']['onkeyup'] = 'phire.changeTitle(this.value);';

        $this->view->form = new Form\Tag($fields);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($tag->toArray());

        if ($this->request->isPost()) {
            $this->view->form->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $tag = new Model\Tag();

                $tag->update($this->view->form->getFields());
                $this->view->id = $tag->id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/tags/edit/'. $tag->id);
            }
        }

        $this->send();
    }

    /**
     * Remove action method
     *
     * @return void
     */
    public function remove()
    {
        if ($this->request->isPost()) {
            $tag = new Model\Tag();
            $tag->remove($this->request->getPost());
        }
        $this->sess->setRequestValue('removed', true);
        $this->redirect(BASE_PATH . APP_URI . '/tags');
    }

    /**
     * Prepare view
     *
     * @param  string $tag
     * @return void
     */
    protected function prepareView($tag)
    {
        $this->viewPath = __DIR__ . '/../../view';
        parent::prepareView($tag);
    }

}
