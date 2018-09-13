<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage	DJ Classifieds Component
* @copyright	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license		http://www.gnu.org/licenses GNU/GPL
* @autor url    http://design-joomla.eu
* @autor email  contact@design-joomla.eu
* @Developer    Lukasz Ciastek - lukasz.ciastek@design-joomla.eu
* 
* 
* DJ Classifieds is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ Classifieds is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ Classifieds. If not, see <http://www.gnu.org/licenses/>.
* 
*/

defined ('_JEXEC') or die('Restricted access');

	$app	= JFactory::getApplication();
	$menus	= $app->getMenu('site');
	/*$menu_item = $menus->getItems('link','index.php?option=com_djclassifieds&view=items',1);
			
	$itemid = ''; 
	if($menu_item){
		$itemid='&Itemid='.$menu_item->id;
	}*/
	
	$page_title = $menus->getActive()->title ;

	if(!$page_title){
		$page_title = JText::_('COM_DJCLASSIFIEDS_REGIONS_TREE');	
	}
	
	$level_limit = $this->par->get('regtree_levels','0');	
	$cols_limit = $this->par->get('regtree_cols','3');
	$items_in_c_type = $this->par->get('regtree_ic','1');		
	$img_level = $this->par->get('regtree_img',0);
	$ll_type = $this->par->get('regtree_last_level_type',0);
	
	$col_limit = 0;
	$regs_c=0;
	$regs_to_display = array();
	if($cols_limit>1){
		if($level_limit>0){
			foreach($this->regs as $r ){
				if($r->level<$level_limit){
					$regs_c++;
					$regs_to_display[]=$r;
				}
			}
			$this->regs =$regs_to_display; 
		}else{
			$regs_c=count($this->regs);	
		}
		
		$col_limit = ceil($regs_c/$cols_limit);
	}
		
	$ct_type  = $this->par->get('regtree_type',null);

$mod_attribs=array();
$mod_attribs['style'] = 'xhtml';	

if($col_limit==0){
	$col_limit = 1;
}
	
?>
<div id="dj-classifieds" class="clearfix">
	<?php
		$modules_djcf = &JModuleHelper::getModules('djcf-top');
		if(count($modules_djcf)>0){
			echo '<div class="djcf-ad-top clearfix">';
			foreach (array_keys($modules_djcf) as $m){
				echo JModuleHelper::renderModule($modules_djcf[$m],$mod_attribs);
			}
			echo'</div>';		
		}	

	 $modules_djcf = &JModuleHelper::getModules('djcf-regtree-top');
			if(count($modules_djcf)>0){
				echo '<div class="djcf-ad-regtree-top clearfix">';
				foreach (array_keys($modules_djcf) as $m){
					echo JModuleHelper::renderModule($modules_djcf[$m],$mod_attribs);
				}
				echo'</div>';		
			}
			?>
			
	<div class="dj-regtree-box cols<?php echo $cols_limit ?>" >	
		<div class="title_top"><h1><?php echo $page_title; ?></h1></div> 
		<div class="dj-regtree-wrap">
		<ul class="dj-regtree col1 reg0 lvl0" >
			<?php 
				$r_count=0;
				$prev_l = 0;
				$col_n=1;
				$prev_id=0;
				//echo '<li>';
				
				foreach($this->regs as $ri => $r){
					
					if($level_limit>0 && $r->level>=$level_limit){
						continue;
					}
										
					if($prev_l>$r->level){
						for($i=0;$i<$prev_l-$r->level;$i++){
							echo '</ul>';	
						}
						
					}else if($prev_l<$r->level){
						$cl_ll='';	
						if($ll_type && $level_limit>1 && $r->level==$level_limit-1){
							$cl_ll=' last_level';
						}
						echo '<ul class="reg'.$prev_id.' lvl'.$r->level.$cl_ll.'">';
					}else if($r_count>0){
						echo '</li>';
					}										
					
						if($r->level==0 && $r_count>0){
							$prev_id = 0;							
							if($ct_type){							
								$col_number = $col_n%$cols_limit;
								if($col_number==0){
									$col_number++;
									echo '</ul><div class="clear_both"></div><ul class="dj-regtree col'.$col_number.' reg'.$prev_id.' lvl'.$r->level.'">';
								}else{
									$col_number++;
									echo '</ul><ul class="dj-regtree col'.$col_number.' reg'.$prev_id.' lvl'.$r->level.'">';
								}							
								$col_n++;	
								
							}else{							
								if(floor($r_count/$col_limit)>=$col_n){
									$col_number = $col_n%$cols_limit;
									$col_number++;
									echo '</ul><ul class="dj-regtree col'.$col_number.' reg'.$prev_id.' lvl'.$r->level.'">';
									$col_n++;	
								}								
							}
						}
					
					
					$prev_l = $r->level;
					$prev_id = $r->id;
					/*if(strstr($reg_path,','.$r->id.',')){
						if($rid==$r->id){
							$cl='class="active current"';
						}else{
							$cl='class="active"';
						}
					}else{*/
						$cl='';
				//	}
					
					
					$items_in_c = '';
					
					if($r->items_count){						
						if($items_in_c_type==2){
							$items_in_c = ' <span>('.$r->items_count.')</span>';		
						}else if($items_in_c_type==1 && $r->level==0){
							$items_in_c = ' <span>('.$r->items_count.')</span>';
						}
					}		
					
					$cl.='reg'.$r->id.' lvl'.$r->level;
					if($cl){
						$cl= 'class="'.$cl.'"';
					}
					
					$ll_coma='';
					if($ll_type && $level_limit>1 && $r->level==$level_limit-1){
						if(isset($this->regs[$ri+1])){
							$ll_coma = ($r->level==$this->regs[$ri+1]->level ? ',' : ''); 
						}	
					}
					
					echo '<li '.$cl.'><a href="'.DJClassifiedsSEO::getRegionRoute($r->id.':'.$r->name).'">'.$r->name.$items_in_c.'</a>'.$ll_coma;	
					$r_count++;
				}		
				if($prev_l>0){
					for($i=0;$i<$prev_l;$i++){
						echo '</li></ul>';	
					}					
				}	
			?>			
		</ul>
		<div class="clear_both"></div>
		</div>
	</div>	
	<?php 
	 	$modules_djcf = &JModuleHelper::getModules('djcf-regtree-bottom');
		if(count($modules_djcf)>0){
			echo '<div class="djcf-ad-regtree-bottom clearfix">';
			foreach (array_keys($modules_djcf) as $m){
				echo JModuleHelper::renderModule($modules_djcf[$m],$mod_attribs);
			}
			echo'</div>';		
		}
	?>	
</div>
