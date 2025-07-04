<?php

namespace Admin;

use database\Database;

class Websetting extends Admin{ 
        
        public function index()
        {
                $db = new DataBase();
                $websetting = $db->select('SELECT * FROM websetting ORDER BY `id` DESC')->fetch();
                require_once(BASE_PATH . '/template/admin/websetting/index.php');
        }

     
        public function edit($id)
        {
                $db = new DataBase();
                $websetting = $db->select('SELECT * FROM websetting WHERE id = ?;', [$id])->fetch();
                require_once(BASE_PATH . '/template/admin/websetting/edit.php');
        }

        public function update($request, $id)
        {
                $db = new DataBase();
                $db->update('categories', $id, array_keys($request), $request);
                $this->redirect('admin/category');

        }

}