<?php

// указываем либо id ресурса, либо ids ресурсов через запятую
// в этом случае берется нужный дочерний ресурс по родителю (например в категориях каталога)

$id = isset($id) ? (int)$id : $modx->documentObject['id'];

$ids = isset($ids) ? $ids : '';
if($ids != ''){
    $rs = $modx->db->query("SELECT `id` FROM ".$modx->getFullTableName('site_content')." WHERE `parent`='$id' AND `id` IN(".$ids.") LIMIT 0,1");
    if($modx->db->getRecordCount($rs) == 1){
        $cid = $modx->db->getValue($rs);
    }
}

$id = isset($cid) ? $cid : $id;
$alias = isset($alias) ? $alias : '';
$url = $modx->makeUrl($id);
$url = rtrim($url,$modx->config['friendly_url_suffix']);
	
return $url."/".$alias.$modx->config['friendly_url_suffix'];
?>