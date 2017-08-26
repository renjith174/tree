<?php
namespace lib;

/**
* 
*/
class Nodes
{
	protected $_db;
	public function __construct($db)
	{
		$this->_db = $db;
	}

	public function getAllNodes(){
		$iSql = "select * from nodes";
    	$exe = $this->_db->query($iSql);
    	return $data = $exe->fetchall();
	}

	public function getSingleNode($id)
	{
		$iSql = "select * from nodes where id = '".$id."' limit 1";
    	$exe = $this->_db->query($iSql);
    	return $exe->fetch();
	}

	public function getParentNode($id)
	{
		$iSql = "select * from nodes where parent_id = '".$id."' ";
    	$exe = $this->_db->query($iSql);
    	return $exe->fetchall();
	}

	public function insertNode($id=0)
	{
		$iSql = "insert into nodes (parent_id) values('".$id."') ";
    	return $this->_db->query($iSql);
	}

	public function updateNode($parentId,$id)
	{
		$iSql = "update nodes set parent_id = '".$parentId."' where id = '".$id."' ";
    	$this->_db->query($iSql);
	}

	public function deleteNode($id)
	{
		
    	$dSql = " delete from nodes where id = '".$id."' ";
    	$exe = $this->_db->query($dSql);
	}

	public function renderMenu($items) {
	    $render = '<ul>';
	    foreach ($items as $item) {
	        $render .= '<li><a class="node-delete" id="nodeid-'.$item->id.'" href="javascript:;" title="delete">' . $item->id . '</a>';
	        if (!empty($item->subs)) {
	            $render .= $this->renderMenu($item->subs);
	        }
	        $render .= '</li>';
	    }

	    return $render . '</ul>';
	}

}
