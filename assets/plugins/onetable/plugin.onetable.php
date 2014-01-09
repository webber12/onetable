<?php
// @params &tmpl_ids=Templates ids comma separated;text;5,8
// @events OnManagerPageInit, OnBeforeDocFormSave, OnDocFormPrerender,OnBeforeTVFormDelete,OnTVFormSave,OnTempFormSave

if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

//$tmpl_ids_array = explode(',',$tmpl_ids);

include_once('class/onetable.php');
$oT = new OneTable($modx, $params);
$DOC = $oT->api;


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
            $oT->api->setTable('table_'.$_POST['template']);
        /*    if($e->params['mode']=='upd'){
                $oT->updateDoc($_POST);
            }
			*/
            if($e->params['mode']=='new'){
                $oT->save2Doc($_POST);
            }			
        }
        break;
    }	
    case 'OnDocFormPrerender':{
        if(isset($_REQUEST['table'])){
            $script = '<script>
				window.addEvent("domready", function(){
					document.getElementById("template").setProperty("name","template3");
					document.getElementById("template").value="'.$_REQUEST['table'].'";templateWarning();
					document.getElementById("template").getParent().getParent().setStyle("display","none");
					})</script>';
            $output .= '<input type="hidden" name="template" value="'.$_REQUEST['table'].'">'.$script;
            $output .= '<input type="hidden" name="table" value="'.$_REQUEST['table'].'">';
        }
        break;
    }	
    case 'OnManagerPageInit':{	
        if($action==27 && isset($_REQUEST['table'])){
            global $_lang, $_style;
            if($oT->checkTemplateById($_REQUEST['id'], $_REQUEST['table'])){
                $tbl = (int)$_REQUEST['table'];
                $onetbl = $modx->getFullTableName('table_'.$tbl);
                $manager_theme = $modx->config['manager_theme'];
                include_once "header.inc.php";
                include_once MODX_BASE_PATH."/assets/plugins/onetable/mutate_content.dynamic.php";
                include_once "footer.inc.php";
                die();
            }
        }
		if($action==5&&isset($_REQUEST['table'])&&$_POST['mode'] == '27'){
			if(isset($_POST['template'])&&$oT->checkTemplate($_POST['template'])){
			    $oT->api->setTable('table_'.$_POST['template']);
                $oT->updateDoc($_POST);
				die();
			}
		}
        break;
    }		
	
    default:
        break;
    }
	
$e->output($output);
