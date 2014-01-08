<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

class OneTable{

public $tableId;
public $tableName;
public $tablePrefix;//�������������� ������� ������ ��� �������� ������ (�� ��������� table_
public $api;
public $modx;
public $tmpl_ids;
public $tmpl_ids_array;
public $plugin_params;

public function __construct($modx, $params)
{
    $this->plugin_params = $params;
    $this->modx = $modx;
    $this->loadAPI($this->modx);
    $this->tmpl_ids	= $this->plugin_params['tmpl_ids'];
    $this->tmpl_ids_array = explode(',', $this->tmpl_ids);
    $this->tv_tmpl_table = $this->modx->getFullTableName('site_tmplvar_templates');
    $this->tmplvars_table = $this->modx->getFullTableName('site_tmplvars');
    $this->tableId = '8';
    $this->tablePrefix = "table_";
    $this->tableName = $this->modx->getFullTableName($this->tablePrefix.$this->tableId);
}

private function loadAPI($modx)
{
    include_once MODX_BASE_PATH.'assets/plugins/CResource/lib/MODxAPI/modCatalog.php';
    $this->api = new modCatalog($modx);
}

public function addTable($id)
{
    $sql="
    CREATE TABLE IF NOT EXISTS ".$this->modx->getFullTableName($this->tablePrefix.$id)." (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL DEFAULT 'document',
  `contentType` varchar(50) NOT NULL DEFAULT 'text/html',
  `pagetitle` varchar(255) NOT NULL DEFAULT '',
  `longtitle` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) DEFAULT '',
  `link_attributes` varchar(255) NOT NULL DEFAULT '',
  `published` int(1) NOT NULL DEFAULT '0',
  `pub_date` int(20) NOT NULL DEFAULT '0',
  `unpub_date` int(20) NOT NULL DEFAULT '0',
  `parent` int(10) NOT NULL DEFAULT '0',
  `isfolder` int(1) NOT NULL DEFAULT '0',
  `introtext` text,
  `content` mediumtext,
  `richtext` tinyint(1) NOT NULL DEFAULT '1',
  `template` int(10) NOT NULL DEFAULT '0',
  `menuindex` int(10) NOT NULL DEFAULT '0',
  `searchable` int(1) NOT NULL DEFAULT '1',
  `cacheable` int(1) NOT NULL DEFAULT '1',
  `createdby` int(10) NOT NULL DEFAULT '0',
  `createdon` int(20) NOT NULL DEFAULT '0',
  `editedby` int(10) NOT NULL DEFAULT '0',
  `editedon` int(20) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `deletedon` int(20) NOT NULL DEFAULT '0',
  `deletedby` int(10) NOT NULL DEFAULT '0',
  `publishedon` int(20) NOT NULL DEFAULT '0',
  `publishedby` int(10) NOT NULL DEFAULT '0',
  `menutitle` varchar(255) NOT NULL DEFAULT '',
  `donthit` tinyint(1) NOT NULL DEFAULT '0',
  `haskeywords` tinyint(1) NOT NULL DEFAULT '0',
  `hasmetatags` tinyint(1) NOT NULL DEFAULT '0',
  `privateweb` tinyint(1) NOT NULL DEFAULT '0',
  `privatemgr` tinyint(1) NOT NULL DEFAULT '0',
  `content_dispo` tinyint(1) NOT NULL DEFAULT '0',
  `hidemenu` tinyint(1) NOT NULL DEFAULT '0',
  `alias_visible` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `parent` (`parent`),
  KEY `aliasidx` (`alias`),
  KEY `typeidx` (`type`),
  FULLTEXT KEY `content_ft_idx` (`pagetitle`,`description`,`content`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;	
    ";
    $q = $this->modx->db->query($sql);
    return true;
}

public function getSQLType($type, $default='TEXT NULL')
{
    $types = array(
				'date'=>"INT(20) NOT NULL DEFAULT '0'",
				'number'=>"DOUBLE NOT NULL DEFAULT '0'"
			);
    return isset($types[$type]) ? $types[$type] : $default;
}

public function getTVInfo($tv_id)
{
    $sql = "SELECT * FROM ".$this->tmplvars_table." WHERE id='".$tv_id."' LIMIT 0,1";
    $info = $this->modx->db->getRow($this->modx->db->query($sql));
    return $info;
}

public function columnExists($column, $table)
{
    if(!$this->tableExists($table)) {return false;}
    else{
        $sql='SHOW COLUMNS FROM '.$table;
        $res = $this->modx->db->query($sql);
        while($row = $this->modx->db->getRow($res)){
            if($row['Field'] == $column) return true;
        }
    }
    return false;
}
  

public function tableExists($table)
{
    $sql = "SHOW TABLES LIKE '$table'";
    $res = $this->modx->db->query($sql);
    if($this->modx->db->getRecordCount($res) > 0) return true;
    return false;
}

public function createColumn($tv_id)
{
    $tv_info = $this->getTVInfo($tv_id);
    $tv_type = $this->getSQLType($tv_info['type'], $default='TEXT NULL');
    $tmpls = array();
    $sql = "SELECT templateid FROM ".$this->tv_tmpl_table." WHERE tmplvarid='".$tv_id."' AND templateid IN(".$this->tmpl_ids.")";
    $q = $this->modx->db->query($sql);
    while($row = $this->modx->db->getRow($q)){
        $tmpls[] = $row['templateid'];
    }
    if(!empty($tmpls)){
        foreach ($tmpls as $tmpl){
            $this->addTable($tmpl);
            $tv_sql = '';
            $table_name = $this->modx->db->config['table_prefix'].$this->tablePrefix.$tmpl;
            if($this->columnExists($tv_info['name'], $table_name)){
                $tv_sql = 'ALTER IGNORE TABLE '.$table_name.' CHANGE `'. $tv_info['name'] .'` `'. $tv_info['name'] .'` '.$this->getSQLType($tv_info['type']);
            }
            else{
                $tv_sql = 'ALTER IGNORE TABLE '.$table_name.' ADD `'. $tv_info['name'] .'` '.$this->getSQLType($tv_info['type']);
            }
            if($tv_sql!=''){
                $q = $this->modx->db->query($tv_sql);
            }
        }
    }
}

public function deleteColumn($tv_id)
{
    $tv_info = $this->getTVInfo($tv_id);
    $tmpls = array();
    $sql = "SELECT templateid FROM ".$this->tv_tmpl_table." WHERE tmplvarid='".$tv_id."' AND templateid IN(".$this->tmpl_ids.")";
    $q = $this->modx->db->query($sql);
    while($row = $this->modx->db->getRow($q)){
        $tmpls[] = $row['templateid'];
    }
    if(!empty($tmpls)){
        foreach ($tmpls as $tmpl){
            $table_name = $this->modx->db->config['table_prefix'].$this->tablePrefix.$tmpl;
            if($this->tableExists($table_name)){
                $tv_sql = '';
                if($this->columnExists($tv_info['name'], $table_name)){
                    $tv_sql = 'ALTER TABLE '.$table_name.' DROP `'. $tv_info['name'] .'`';
                    $q = $this->modx->db->query($tv_sql);
                }
            }
        }
    }
}

public function checkTemplateById($id, $table=false)
{
    $template = false;
    if(isset($_REQUEST['template']) && (int)$_REQUEST['template']!=0){
	    $template=(int)$_REQUEST['template'];
	}
    if(!$template&&$table){
        $table_name = $this->modx->db->config['table_prefix'].$this->tablePrefix.$table;
        $template = $this->modx->db->getValue($this->modx->db->query("SELECT template FROM $table_name WHERE id='$id' LIMIT 0,1"));
    }
    if($template && in_array($template,$this->tmpl_ids_array)) return true;
    else return false;
}

public function checkTemplate($template)
{
    if(in_array($template,$this->tmpl_ids_array)) return true;
    else return false;
}

public function getTVNames($template_id)
{
    $TVNames = array();
    $q=$this->modx->db->query("SELECT a.id,a.name,a.default_text FROM ".$this->tmplvars_table." a,".$this->tv_tmpl_table." b WHERE a.id=b.tmplvarid AND b.templateid=".$template_id);
    while($row=$this->modx->db->getRow($q)){
        $TVNames[$row['id']]['name'] = $row['name'];
        $TVNames[$row['id']]['default_text'] = $row['default_text'];
    }
    return $TVNames;
}

public function updateDoc($post, $DOC)
{
    $data = $this->prepareData($_POST);
    $edit = $DOC->edit($_POST['id'])->fromArray($data)->save();
    echo 'updated';
    die();
}

public function save2Doc($post, $DOC)
{
    $data = $this->prepareData($_POST);
    $edit = $DOC->create($data)->save();
    echo 'saved';
    die();
}

public function prepareData($tmp)
{
    $TVNames = $this->getTVNames($tmp['template']);
    $data = array();
    foreach($tmp as $k=>$v){
        if(strpos($k,'tv')===0){
            $k=str_replace('tv','',$k);
            if(isset($TVNames[$k])){
                $data[$TVNames[$k]['name']] = is_array($v)?implode('||',$v):($v==''?$TVNames[$k]['default_text']:$v);	
            }else{
                $data[$k] = is_array($v) ? implode('||',$v) : $v;
            }
        }else{
            if($k=='ta'){$k = 'content';}
            $data[$k] = is_array($v) ? implode('||',$v) : $v;
        }
    }
	
    foreach($TVNames as $k=>$v){ //hack for empty checkboxes & radios
        if(!isset($tmp['tv'.$k])){
            $data[$v['name']] = $v['default_text'];
        }
    }
    return $data;
}

public function getTVFromContent($content = array(), $template_id)
{
    $alltvs = array();
    $tvs = array();
    $q = $this->modx->db->query("SELECT * FROM ".$this->tmplvars_table." a,".$this->tv_tmpl_table." b WHERE a.id=b.tmplvarid AND b.templateid=".$template_id." ORDER BY b.rank ASC");
    while($row=$this->modx->db->getRow($q)){
        if(isset($content[$row['name']])){
            $tvs[$row['name']] = $row;
            $tvs[$row['name']]['value'] = $content[$row['name']];
        }
    }
    return $tvs;	
}

}//end class
