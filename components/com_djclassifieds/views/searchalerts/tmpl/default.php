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
$toolTipArray = array('className'=>'djcf');
JHTML::_('behavior.tooltip', '.Tips1', $toolTipArray);
$par = JComponentHelper::getParams( 'com_djclassifieds' );
$app = JFactory::getApplication();
$main_id= JRequest::getVar('cid', 0, '', 'int');
$it= JRequest::getVar('Itemid', 0, '', 'int');
$document = JFactory::getDocument();


$order = JRequest::getCmd('order', $par->get('items_ordering','date_e'));
$ord_t = JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
$ord_dir = JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
if($ord_t=="desc"){
	$ord_t='asc';
}else{
	$ord_t='desc';
}

$sw = JRequest::getVar('search', '', '', 'string');
$uid	= JRequest::getVar('uid', 0, '', 'int');

	$menus	= $app->getMenu('site');
	$menu_item = $menus->getItems('link','index.php?option=com_djclassifieds&view=items',1);
		
	$itemid = ''; 
	if($menu_item){
		$itemid='&Itemid='.$menu_item->id;
	}
	
	$menu_item_new = $menus->getItems('link','index.php?option=com_djclassifieds&view=additem',1);
	$itemid_new = '';
	if($menu_item_new){
		$itemid_new='&Itemid='.$menu_item_new->id;
	}else{
		$itemid_new = $itemid;
	}
	
$renew_date = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s"), date("m")  , date("d")+$par->get('renew_days','3'), date("Y"))); 
$r=TRUE;

$style = '#dj-classifieds .users_search_alerts .dj-items-table2 .item_col{border-bottom:3px solid #e8e8e8;padding-bottom: 5px;}#dj-classifieds .users_search_alerts .dj-items-table2 .col_row{margin-bottom: 10px;text-align: left;}#dj-classifieds .users_search_alerts .dj-items-table2 .col_row_label {margin-right:10px;}#dj-classifieds .users_search_alerts .dj-items-table2 .col_row span.col_row_val{font-weight: bold;}#dj-classifieds .users_search_alerts .sa_edetails_box .sa_edetail_item{display:inline-block;}#dj-classifieds .users_search_alerts .sa_edetails_box .sa_edetail_sep{margin:0 5px 0 2px;}#dj-classifieds .users_search_alerts .sa_edetails_box .sa_edetail_item .sa_edetail_name{margin-right:5px;}#dj-classifieds .users_search_alerts .dj-items-table2 .col_row.col_row_buttons a{display: inline-block;margin: 0 20px 0 0;/*padding: 0 0 0 22px;float:right;*/text-decoration: none;border:none;text-transform: none;font-weight: normal;} #dj-classifieds .save_search_link{text-align: right;margin:10px 0;}';
$document->addStyleDeclaration( $style );


?>
<div id="dj-classifieds" class="clearfix djcftheme-<?php echo $par->get('theme','default');?>">
	<div class="title_top"><h1>
		<?php	echo JText::_('COM_DJCLASSIFIEDS_YOUR_SAVED_SEARCH');?>
	</h1></div>
	<div class="users_search_alerts">		
		<div class="dj-users_search_alerts">		
			<div class="dj-items-table2">	
				<?php
				$r=TRUE;	
				foreach($this->items as $i){
					$row = $r==TRUE ? '0' : '1';
					$r=!$r;
					echo '<div class="item_row row'.$row.'">';
						echo '<div class="item_col first">';
							echo '<div class="col_row">';
								echo JText::_('COM_DJCLASSIFIEDS_SEARCH_PHRASE').' : <span>'.$i->phrase.'</span>';
							echo '</div>';
							echo '<div class="col_row col_row_buttons">';
								echo '<a class="delete" href="javascript:void(0)" onclick="confirm_del('.$i->id.')" >'.JText::_('COM_DJCLASSIFIEDS_DELETE').'</a>';
								echo '<a target="_blank" class="search_results" href="'.base64_decode($i->search_url).'">'.JText::_('COM_DJCLASSIFIEDS_SEARCH_RESULTS').'</a>';								
							echo '</div>';
						echo '</div>';
						echo '<div class="item_col">';
							echo '<div class="col_row"><span class="col_row_label">'.JText::_('COM_DJCLASSIFIEDS_CATEGORY').':</span><span class="col_row_val">'.$i->category.'</span></div>';
							echo '<div class="col_row"><span class="col_row_label">'.JText::_('COM_DJCLASSIFIEDS_LOCALIZATION').':</span><span class="col_row_val">'.$i->region.'</span></div>';
							echo '<div class="col_row"><span class="col_row_label">'.JText::_('COM_DJCLASSIFIEDS_EXTRA_DETAILS').':</span><span class="col_row_val sa_edetails_box">';
								$sq = json_decode($i->search_query);
								$edet = 0; 

								if(isset($sq->se_type_id) && $sq->se_type_id && isset($this->types[$sq->se_type_id])){
									/*if($edet){
										echo '<span class="sa_edetail_sep">,</span>';	
									}*/
									echo '<span class="sa_edetail_item">';
										echo '<span class="sa_edetail_name">'.JText::_('COM_DJCLASSIFIEDS_TYPE').':</span>';
										echo '<span class="sa_edetail_val">'.$this->types[$sq->se_type_id]->name.'</span>';
									echo '</span>';
									$edet++;											
								}
								
								if(isset($sq->se_price_f) && $sq->se_price_f){
									if($edet){
										echo '<span class="sa_edetail_sep">,</span>';	
									}
									echo '<span class="sa_edetail_item">';
										echo '<span class="sa_edetail_name">'.JText::_('COM_DJCLASSIFIEDS_PRICE').' ('.strtolower(JText::_('COM_DJCLASSIFIEDS_FROM')).'):</span>';
										echo '<span class="sa_edetail_val">'.DJClassifiedsTheme::priceFormat($sq->se_price_f).'</span>';
									echo '</span>';
									$edet++;											
								}											

								if(isset($sq->se_price_t) && $sq->se_price_t){
									if($edet){
										echo '<span class="sa_edetail_sep">,</span>';	
									}
									echo '<span class="sa_edetail_item">';
										echo '<span class="sa_edetail_name">'.JText::_('COM_DJCLASSIFIEDS_PRICE').' ('.strtolower(JText::_('COM_DJCLASSIFIEDS_TO')).'):</span>';
										echo '<span class="sa_edetail_val">'.DJClassifiedsTheme::priceFormat($sq->se_price_t).'</span>';
									echo '</span>';
									$edet++;											
								}
								
								foreach($this->cfields as $field){
									//echo $sq->{"se_$field->id"};die();
									if(isset($sq->{"se_$field->id"})){
										if($sq->{"se_$field->id"}){
											if($edet){
												echo '<span class="sa_edetail_sep">,</span>';	
											}
											echo '<span class="sa_edetail_item">';
												echo '<span class="sa_edetail_name">'.$field->label.':</span>';
												if($field->type=='checkbox' || $field->type=='radio' || $field->type=='selectlist'){
													echo '<span class="sa_edetail_val">'.str_replace(',', ', ', $sq->{"se_$field->id"}).'</span>';
												}else{
													echo '<span class="sa_edetail_val">'.$sq->{"se_$field->id"}.'</span>';	
												}
											echo '</span>';
											$edet++;											
										}
									}else{
										if(isset($sq->{'se_'.$field->id.'_min'})){
											if($sq->{'se_'.$field->id.'_min'}){
												if($edet){
													echo '<span class="sa_edetail_sep">,</span>';	
												}
												echo '<span class="sa_edetail_item">';
													echo '<span class="sa_edetail_name">'.$field->label.' ('.strtolower(JText::_('COM_DJCLASSIFIEDS_FROM')).'):</span>';
													echo '<span class="sa_edetail_val">'.$sq->{'se_'.$field->id.'_min'}.'</span>';
												echo '</span>';
												$edet++;											
											}
										}
										if(isset($sq->{'se_'.$field->id.'_max'})){
											if($sq->{'se_'.$field->id.'_max'}){
												if($edet){
													echo '<span class="sa_edetail_sep">,</span>';	
												}
												echo '<span class="sa_edetail_item">';
													echo '<span class="sa_edetail_name">'.$field->label.' ('.strtolower(JText::_('COM_DJCLASSIFIEDS_TO')).'):</span>';
													echo '<span class="sa_edetail_val">'.$sq->{'se_'.$field->id.'_max'}.'</span>';
												echo '</span>';
												$edet++;											
											}
										}
									}
								}

								if(isset($sq->se_only_auctions) && $sq->se_only_auctions){
									if($edet){echo '<span class="sa_edetail_sep">,</span>';}
									echo '<span class="sa_edetail_item">';
										echo '<span class="sa_edetail_name">'.JText::_('MOD_DJCLASSIFIEDS_SEARCH_SHOW_ONLY_AUCTIONS').':</span>';
										echo '<span class="sa_edetail_val">'.strtolower(JText::_('JYES')).'</span>';
									echo '</span>';
									$edet++;											
								}
								
								if(isset($sq->se_only_buynow) && $sq->se_only_buynow){
									if($edet){echo '<span class="sa_edetail_sep">,</span>';}
									echo '<span class="sa_edetail_item">';
										echo '<span class="sa_edetail_name">'.JText::_('MOD_DJCLASSIFIEDS_SEARCH_SHOW_ONLY_BUYNOW').':</span>';
										echo '<span class="sa_edetail_val">'.strtolower(JText::_('JYES')).'</span>';
									echo '</span>';
									$edet++;											
								}

								if(isset($sq->se_only_img) && $sq->se_only_img){
									if($edet){echo '<span class="sa_edetail_sep">,</span>';}
									echo '<span class="sa_edetail_item">';
										echo '<span class="sa_edetail_name">'.JText::_('MOD_DJCLASSIFIEDS_SEARCH_SHOW_ONLY_WITH_IMAGES').':</span>';
										echo '<span class="sa_edetail_val">'.strtolower(JText::_('JYES')).'</span>';
									echo '</span>';
									$edet++;											
								}
								
								if(isset($sq->se_only_video) && $sq->se_only_video){
									if($edet){echo '<span class="sa_edetail_sep">,</span>';}
									echo '<span class="sa_edetail_item">';
										echo '<span class="sa_edetail_name">'.JText::_('MOD_DJCLASSIFIEDS_SEARCH_SHOW_ONLY_WITH_VIDEO').':</span>';
										echo '<span class="sa_edetail_val">'.strtolower(JText::_('JYES')).'</span>';
									echo '</span>';
									$edet++;											
								}
								
								if(isset($sq->se_also_18) && $sq->se_also_18){
									if($edet){echo '<span class="sa_edetail_sep">,</span>';}
									echo '<span class="sa_edetail_item">';
										echo '<span class="sa_edetail_name">'.JText::_('MOD_DJCLASSIFIEDS_SEARCH_SHOW_RESTRICTION_18_LABEL').':</span>';
										echo '<span class="sa_edetail_val">'.strtolower(JText::_('JYES')).'</span>';
									echo '</span>';
									$edet++;											
								}

								if(!$edet){
									echo '- - -';
								}

								//echo '<pre>';print_r($sq);die();
							echo '</span></div>';
						echo '</div>';														
					echo '</div>';
					
				}
				?>	
			</div>			
		</div>
		<?php if($this->pagination->getPagesLinks()){
			echo '<div class="pagination" >';
				echo $this->pagination->getPagesLinks();
			echo '</div>';
		}?>	
		
	</div>	

</div>
<script type="text/javascript">
	function confirm_del(id){	
		var answer = confirm ('<?php echo str_replace("'","\'",JText::_('COM_DJCLASSIFIEDS_DELETE_SAVED_SEARCH'));?>');
		if (answer){
			 window.location="index.php?option=com_djclassifieds&view=searchalerts&task=deleteSearch&id="+id+"&Itemid=<?php echo $it;?>";	
		}
	}
	
	
</script>