<?php

namespace Admin;

use database\Database;

class Post extends Admin
{

    public function index()
    {
        $db = new  DataBase();
        $posts = $db->select('SELECT * FROM posts ORDER BY `id` DESC');

        require_once(BASE_PATH . '/template/admin/posts/index.php');
    }

    public function create()
    {
//        dd('h1');
        require_once(BASE_PATH . '/template/admin/posts/create.php');

    }

    public function store($request)
    {
        $db = new DataBase();
        $db->insert('categories', array_keys($request), $request);
        $this->redirect('/admin/category');
    }

    public function edit($id)
    {
        $db = new  DataBase();
        $category = $db->select('SELECT * FROM categories where  id = ?', [$id])->fetch();
        require_once(BASE_PATH . '/template/admin/categories/edit.php');

    }

    public function update($request, $id)
    {
        $db = new DataBase();
        $db->update('categories', $id, array_keys($request), $request);
        $this->redirect('admin/category');

    }

    public function destroy($id)
    {
        $db = new  DataBase();
        $db->delete('categories', $id);
        $this->redirect('/admin/category');

    }
}