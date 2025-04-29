<?php

namespace Admin;

use database\Database;

class Banner extends Admin{

    public function index()
    {
        $db = new DataBase();
        $banners = $db->select('SELECT * FROM banners ORDER BY `id` DESC');
        require_once(BASE_PATH . '/template/admin/banners/index.php');
    }

    public function create()
    {

        require_once(BASE_PATH . '/template/admin/banners/create.php');

    }

    public function store($request)
    {
        $db = new DataBase();
        $request['image'] = $this->saveImage($request['image'], 'banner-image');
        if($request['image'])
        {
            $db->insert('banners', array_keys($request), $request);
            $this->redirect('admin/banner');
        }
        else{
            $this->redirect('admin/banner');
        }

    }

    public function edit($id)
    {
        $db = new DataBase();
        $category = $db->select('SELECT * FROM categories WHERE id = ?;', [$id])->fetch();
        require_once(BASE_PATH . '/template/admin/categories/edit.php');
    }

    public function update($request, $id)
    {
        $db = new DataBase();
        $db->update('categories', $id, array_keys($request), $request);
        $this->redirect('admin/category');

    }

    public function delete($id)
    {
        $db = new DataBase();
        $db->delete('categories', $id);
        $this->redirect('admin/category');
    }
}