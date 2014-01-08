<?php
// @params &tmpl_ids=Templates ids comma separated;text;5,8
// @events OnManagerPageInit, OnBeforeDocFormSave, OnDocFormPrerender,OnBeforeTVFormDelete,OnTVFormSave,OnTempFormSave

if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

$tmpl_ids_array=explode(',',$tmpl_ids);

//include_once MODX_BASE_PATH.'assets/plugins/CResource/lib/MODxAPI/modCatalog.php';
//$DOC=new modCatalog($modx);

include_once('class.onetable.php');
$oT=new oneTable($modx,$params);
$DOC=$oT->api;


$e = &$modx->event;
$output = '';
switch($e->name){
	case 'OnTempFormSave':{
		if(isset($modx->event->params['id'])&&in_array($modx->event->params['id'],$oT->tmpl_ids_array)){
			$oT->addTable($modx->event->params['id']);
		}
		break;
	}
	
	case 'OnTVFormSave':{
		$oT->createColumn($modx->event->params['id']);
		break;
	}
	
	case 'OnBeforeTVFormDelete':{
		$oT->deleteColumn($modx->event->params['id']);
		break;
	}
	
	case 'OnBeforeDocFormSave':{
		if(isset($_POST['template'])&&$oT->checkTemplate($_POST['template'])){
			$DOC->table='table_'.$_POST['template'];
			if($e->params['mode']=='upd'){
				$oT->updateDoc($_POST,$DOC);
			}
			if($e->params['mode']=='new'){
				$oT->save2Doc($_POST,$DOC);
			}			
		}
		break;
	}
	
	case 'OnDocFormPrerender':{
		if(isset($_REQUEST['table'])){
			$script='<script>
				window.addEvent("domready", function(){
					document.getElementById("template").setProperty("name","template3");
					document.getElementById("template").getParent().getParent().setStyle("display","none");
					})</script>';
			$output.='<input type="hidden" name="template" value="'.$_REQUEST['table'].'">'.$script;
		}
		break;
	}

	
	
	case 'OnManagerPageInit':{	
		if($action==27&&isset($_GET['table'])){
			global $_lang,$_style;
			if($oT->checkTemplateById($_GET['id'],$_GET['table'])){
				$tbl=(int)$_GET['table'];
				$onetbl = $modx->getFullTableName('table_'.$tbl);
				$manager_theme=$modx->config['manager_theme'];
				include_once "header.inc.php";
				include_once MODX_BASE_PATH."/assets/plugins/onetable/mutate_content.dynamic.php";
				include_once "footer.inc.php";
				die();
			}
		}
		break;
	}		
	
	default:
		break;
	}
	
$e->output($output);