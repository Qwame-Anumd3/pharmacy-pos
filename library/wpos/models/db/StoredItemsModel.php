<?php
/**
 * StoredItemsModel is part of Wallace Point of Sale system (WPOS) API
 *
 * StoredItemsModel extends the DbConfig PDO class to interact with the config DB table
 *
 * WallacePOS is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * WallacePOS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details:
 * <https://www.gnu.org/licenses/lgpl.html>
 *
 * @package    wpos
 * @copyright  Copyright (c) 2014 WallaceIT. (https://wallaceit.com.au)

 * @link       https://wallacepos.com
 * @author     Michael B Wallace <micwallace@gmx.com>
 * @since      File available since 11/23/13 10:36 PM
 */

class StoredItemsModel extends DbConfig
{

    /**
     * @var array available columns
     */
    protected $_columns = ['id' ,'data', 'categoryid', 'name', 'taxid', 'reorderPoint'];

    /**
     * Init DB
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $data
     * @return bool|string Returns false on an unexpected failure, returns -1 if a unique constraint in the database fails, or the new rows id if the insert is successful
     */
    public function create($data)
    {
        $dataObj = json_encode(['name'=>$data->name, 'description'=>$data->description, 'categoryid'=>$data->categoryid, 'taxid'=>$data->taxid, 'reorderPoint'=>$data->reorderPoint, 'stockType'=>$data->stockType]);

        $sql          = "INSERT INTO stored_items (`data`, `categoryid`, `name`, `description`, `taxid`, `reorderPoint`, `stockType`) VALUES (:data, :categoryid, :name, :description, :taxid, :reorderPoint, :stockType);";
        $placeholders = [":data"=>$dataObj, ":categoryid"=>$data->categoryid, ":name"=>$data->name, ":description"=>$data->description, ":taxid"=>$data->taxid, ":reorderPoint"=>$data->reorderPoint, ":stockType"=>$data->stockType];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param null $Id
     * @return array|bool Returns false on an unexpected failure or an array of selected rows
     */
    public function get($Id = null) {
        $sql = 'SELECT * FROM stored_items';
        $placeholders = [];
        if ($Id !== null) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            }
            $sql .= ' id = :id';
            $placeholders[':id'] = $Id;
        }

        $items = $this->select($sql, $placeholders);
        if ($items===false)
            return false;

        foreach($items as $key=>$item){
            $data = json_decode($item['data'], true);
            $data['id'] = $item['id'];
            $items[$key] = $data;
        }

        return $items;
    }

    // TODO:: Remove this function
    public function getIdForName($name){

        $sql          = "SELECT * FROM stored_items WHERE name=:storeditemid;";
        $placeholders = [":storeditemid"=>$name];

        $items = $this->select($sql, $placeholders);
        if ($items===false)
            return false;

        foreach($items as $key=>$item){
            $data = json_decode($item['data'], true);
            $data['id'] = $item['id'];
            $items[$key] = $data;
        }

        return $items;
    }

    /**
     * @param $data
     * @return array|bool Returns false on an unexpected failure or an array of selected rows
     */
    public function getDuplicate($data) {
        $sql = 'SELECT * FROM stored_items WHERE `name`=:name AND `categoryid`=:categoryid';
        $placeholders = [":name"=>$data->name, ":categoryid"=>$data->categoryid];

        $items = $this->select($sql, $placeholders);
        if ($items===false)
            return false;
        else
            return sizeof($items);
    }

    /**
     * @param $id
     * @param $data
     * @return bool|int Returns false on an unexpected failure or the number of rows affected by the update operation
     */
    public function edit($id, $data){

        $sql = "UPDATE stored_items SET data= :data, categoryid= :categoryid, name= :name, taxid= :taxid, reorderPoint= :reorderPoint, stockType= :stockType WHERE id= :id;";
        $placeholders = [":id"=>$id, ":data"=>json_encode($data), ":categoryid"=>$data->categoryid, ":name"=>$data->name, ":taxid"=>$data->taxid, ":reorderPoint"=>$data->reorderPoint, ":stockType"=>$data->stockType];

        return $this->update($sql, $placeholders);
    }

    /**
     * @param integer|array $id
     * @return bool|int Returns false on an unexpected failure or the number of rows affected by the delete operation
     */
    public function remove($id){

        $placeholders = [];
        $sql = "DELETE FROM stored_items WHERE ";
        if (is_numeric($id)){
            $sql .= " `id`=:id;";
            $placeholders[":id"] = $id;
        } else if (is_array($id)) {
            $id = array_map([$this->_db, 'quote'], $id);
            $sql .= " `id` IN (" . implode(', ', $id) . ");";
        } else {
            return false;
        }

        return $this->delete($sql, $placeholders);
    }

}