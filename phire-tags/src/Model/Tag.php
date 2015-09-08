<?php

namespace Phire\Tags\Model;

use Phire\Tags\Table;
use Phire\Model\AbstractModel;
use Pop\Filter\Slug;

class Tag extends AbstractModel
{

    /**
     * Get all tags
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $order = $this->getSortOrder($sort, $page);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            return Table\Tags::findAll([
                'offset' => $page,
                'limit'  => $limit,
                'order'  => $order
            ])->rows();
        } else {
            return Table\Tags::findAll([
                'order'  => $order
            ])->rows();
        }
    }

    /**
     * Get tag by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $tag = Table\Tags::findById($id);
        if (isset($tag->id)) {
            $this->data = array_merge($this->data, $tag->getColumns());
        }
    }

    /**
     * Get tag from slug
     *
     * @param  string  $slug
     * @return void
     */
    public function getBySlug($slug)
    {
        $tag = Table\Tags::findBy(['slug' => $slug]);
        if (isset($tag->id)) {
            $this->data = array_merge($this->data, $tag->getColumns());
        }
    }

    /**
     * Save new tag
     *
     * @param  array $fields
     * @return void
     */
    public function save(array $fields)
    {
        $tag = new Table\Tags([
            'title' => $fields['title'],
            'slug'  => Slug::filter($fields['title'])
        ]);
        $tag->save();

        $this->data = array_merge($this->data, $tag->getColumns());
    }

    /**
     * Update an existing tag
     *
     * @param  array $fields
     * @return void
     */
    public function update(array $fields)
    {
        $tag = Table\Tags::findById((int)$fields['id']);
        if (isset($tag->id)) {
            $tag->title = $fields['title'];
            $tag->slug  = Slug::filter($fields['title']);
            $tag->save();

            $this->data = array_merge($this->data, $tag->getColumns());
        }
    }

    /**
     * Remove a tag
     *
     * @param  array $fields
     * @return void
     */
    public function remove(array $fields)
    {
        if (isset($fields['rm_tags'])) {
            foreach ($fields['rm_tags'] as $id) {
                $tag = Table\Tags::findById((int)$id);
                if (isset($tag->id)) {
                    $tag->delete();
                }
            }
        }
    }

    /**
     * Determine if list of tags has pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\Tags::findAll()->count() > $limit);
    }

    /**
     * Get count of tags
     *
     * @return int
     */
    public function getCount()
    {
        return Table\Tags::findAll()->count();
    }

}