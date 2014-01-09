<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

require_once 'content.filter.php';
/**
 * Filters DocLister results by value of a given MODx Template Variables (TVs).
 * @author kabachello <kabachnik@hotmail.com>
 *
 */
class tv_DL_filter extends content_DL_filter{
	private $tv_id;

	protected function parseFilter($filter) {
        $return = false;
        // use the parsing mechanism of the content filter for the start
        if (parent::parseFilter($filter)){
            // now add some variables specific to the TV-filter
            // get the id of the TV
            /**
             * @var $ext tv_DL_Extender
             */
            if($ext = $this->DocLister->getExtender('tv',true)){
                $ext->getAllTV_Name();
                $tmp = array_keys($ext->getTVid($this->field));
                if(count($tmp)==1){
                    $this->tv_id = $tmp[0];
                }
            }
            if(!$this->tv_id){
                $tvid = $this->modx->db->query("SELECT id FROM ".$this->DocLister->getTable('site_tmplvars')." WHERE `name` = '".$this->modx->db->escape($this->field)."'");
                $this->tv_id = intval($this->modx->db->getValue($tvid));
            }

            if (!$this->tv_id){
                $this->DocLister->debug->warning('DocLister filtering by template variable "' . $this->DocLister->debug->dumpData($this->field) . '" failed. TV not found!');
            }else{
                // create the alias for the join
                $alias = 'dltv_' . $this->field;
                if($this->totalFilters>0){
                    $alias .= '_'.$this->totalFilters;
                }
                $this->setTableAlias($alias);
                $this->field = 'value';
                $return = true;
            }
        };
		
		return $return;
	}

	public function get_join(){
        $alias = $this->getTableAlias();
        $join = 'LEFT JOIN '.$this->DocLister->getTable('site_tmplvar_contentvalues',$alias).' ON `'.$alias.'`.`contentid`=`'.content_DL_filter::TableAlias.'`.`id` AND `'.$alias.'`.`tmplvarid`='.$this->tv_id;
        return $join;
	}
}
?>