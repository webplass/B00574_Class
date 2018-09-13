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

//$par = JComponentHelper::getParams( 'com_djclassifieds' );
$par = DJClassifiedsParams::getParams();
$app = JFactory::getApplication();
$Itemid= JRequest::getVar('Itemid', 0, '', 'int');

$menuitem = $app->getMenu()->getActive();
$user_type = $menuitem ? $menuitem->params->get('user_type','11') : '11';
$se= JRequest::getVar('se', 0, '', 'int');
$sw = htmlspecialchars(JRequest::getVar('search',''), ENT_COMPAT, 'UTF-8');
$se_link='';
if($se){
	$se_link='&se=1';	
	if($sw){
		$se_link .= '&search='.$sw; 
	}
	foreach($_GET as $key=>$get_v){
		if(strstr($key, 'se_')){
			if(is_array($get_v)){
				for($gvi=0;$gvi<count($get_v);$gvi++){
					$se_link .= '&'.$key.'[]='.htmlspecialchars($get_v[$gvi], ENT_COMPAT, 'UTF-8');
				}
			}else{
				$se_link .= '&'.$key.'='.htmlspecialchars($get_v, ENT_COMPAT, 'UTF-8');
			}
			
		}
	}
}

$order = JRequest::getCmd('order', $par->get('items_ordering','date_e'));
$ord_t = JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
if($ord_t=="desc"){
	$ord_t='asc';
}else{
	$ord_t='desc';
}

?>
<div id="dj-classifieds" class="clearfix profiles_list djcftheme-<?php echo $par->get('theme','default');?>">
<?php /*	
	<div class="title_top"><h1>
		<?php echo JText::_('COM_DJCLASSIFIEDS_SEARCH_ALL'); ?>
	</h1></div>
	
	<div class="profiles_search">
	
		<form method="post" name="djForm" class="form" enctype="multipart/form-data">		
			
			<div class="filter-wrapper search-word">
				<label><?php echo JText::_('COM_DJCLASSIFIEDS_PROFILES_SEARCH_LABEL'); ?></label>
				<input type="text" size="25" name="search" class="inputbox" value="<?php echo JRequest::getVar('search',''); ?>" placeholder="<?php echo JText::_('COM_DJCLASSIFIEDS_PROFILES_SEARCH_PLACEHOLDER'); ?>" />
			</div>
			
			<?php /*?>
			<div class="filter-wrapper">
				<label><?php echo JText::_('COM_DJCLASSIFIEDS_PROFILES_REGION_LABEL'); ?></label>
				<select name="region_id">
					<option value=""><?php echo JText::_('COM_DJCLASSIFIEDS_PROFILES_SELECT_REGION');?></option>	
					<?php //echo JHtml::_('select.options', DJClassifiedsRegion::getRegSelectNoCities(), 'value', 'text', JRequest::getVar('region_id',''));				
						foreach($this->regions as $reg){
							$r_name = str_ireplace("'", "&apos;", $reg->name);
							for($lev=0;$lev<$reg->level;$lev++){
								$r_name ="- ".$r_name;
							}
							echo '<option value="'.$reg->id.'">'.$r_name.'</option>';
						}				
					?>
					
				</select>
			</div>
			<?php * / ?>
			
			<div class="filter-wrapper">
				<button class="button" type="submit"  ><?php echo JText::_('COM_DJCLASSIFIEDS_PROFILES_SEARCH_BUTTON'); ?></button>
			</div>
			
		    <input type="hidden" name="option" value="com_djclassifieds" />
		    <input type="hidden" name="view" value="profiles" />
		    <input type="hidden" name="Itemid" value="<?php echo $it; ?>" />
		   <div class="clear_both"></div>
		</form>
	</div>	
<?php */ ?>
	<div class="title_top">
	<h1>
		<?php echo JText::_('COM_DJCLASSIFIEDS_ALL_PROFILES'); ?>
	</h1>
	</div>
	<?php /* ?>
	<div class="dj-items_order_by">
		<div class="dj-items_order_by_in">
			<div class="dj-items_order_by_label"><?php echo JText::_('COM_DJCLASSIFIEDS_SORT_BY')?></div>															
			<div class="dj-items_order_by_values">
				<?php if($order=="cat"){ $sort_class = ($ord_t=="desc")? 'active active_asc' : 'active active_desc'; }else{$sort_class='';}?>						
				<a class="<?php echo $sort_class; ?>" href="<?php echo JRoute::_('index.php?option=com_djclassifieds&view=profiles&order=cat&ord_t='.$ord_t.'&Itemid='.$Itemid); ?>">
					<?php echo JText::_('COM_DJCLASSIFIEDS_CATEGORY'); ?>
				</a>
				<span class="item_orderby_separator"></span> 	
				<?php if($order=="loc"){ $sort_class = ($ord_t=="desc")? 'active active_asc' : 'active active_desc'; }else{$sort_class='';}?>
				<a class="<?php echo $sort_class; ?>" href="<?php echo JRoute::_('index.php?option=com_djclassifieds&view=profiles&order=loc&ord_t='.$ord_t.'&Itemid='.$Itemid); ?>">
					<?php echo JText::_('COM_DJCLASSIFIEDS_LOCALIZATION');?>
				</a>
			</div>
		</div>
	</div>	
	<?php */ ?>		

	<div class="profiles_items">	
		
		<div class="dj-useradverts djprofiles clearfix">
			
			<?php foreach($this->items as $i){ ?>
				
				<?php
					$u_slug = $i->u_id.':'.DJClassifiedsSEO::getAliasName($i->username);
					$u_url = JRoute::_('index.php?option=com_djclassifieds&view=profile&uid='.$u_slug.DJClassifiedsSEO::getUserProfileItemid());
				?>
				
				<div class="span6 profile_item"><div class="profile_item_in">
					
					<div class="span4 left-column">
						
						<?php 
						if($par->get('profile_avatar_source','')){
									echo DJClassifiedsSocial::getUserAvatar($item->user_id,$par->get('profile_avatar_source',''),'S');
						}else if($i->path){ ?>
							<div class="img-wrapper">
								<a href="<?php echo $u_url; ?>">
									<img src="<?php echo JURI::base(true).$i->path; ?>">
								</a>
							</div>
						<?php 
						}else{
							echo '<img style="width:100%" src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/default_profile_s.png" />';									
						} ?>
						
						<?php /* ?>
						<?php if($i->address): ?>
							<div class="address">
								<?php echo $i->address; ?>
							</div>
						<?php endif; ?>
						<?php */ ?>
					</div>
					
					<div class="span8 right-column">
						<div class="title">
							<a href="<?php echo $u_url; ?>">
								<h3>
									<?php echo $i->name; ?>
								</h3>
							</a>
						</div>
						
						<?php if($i->r_name){ ?>
						<div class="region">
							<?php echo $i->r_name; ?>
						</div>
						<?php } ?>
						<?php /* if($i->c_name){ ?>
							<div class="category">
								<?php echo $i->c_name; ?>
							</div>
						<?php } */?>
						
						<?php if(count($i->profile_fields)){
							//echo '<pre>';print_r($i->profile_fields);die();
							
							 ?>
							<div class="profile-fields">
								<?php foreach($i->profile_fields as $pf){ ?>
									<div class="cf_box">		
										<?php 
										echo '<span class="label_title">'.$pf->label.' : </span>';
											if($pf->type=='checkbox'){
												echo str_ireplace(';', ', ', substr($field,1,-1));
											}else if($pf->type=='link'){
												if($field==''){echo '---'; }
												else{
													if(strstr($field, 'http://') || strstr($field, 'https://')){
														echo '<a '.$pf->params.' href="'.$field.'">'.str_ireplace(array("http://","https://"), array('',''), $field).'</a>';;
													}else{
														echo '<a '.$pf->params.' href="http://'.$field.'">'.$field.'</a>';;
													}
												}
											}else if($pf->type=='date'){
												echo DJClassifiedsTheme::formatDate(strtotime($pf->value_date));
											}else{
												echo $pf->value;	
											}?>
									</div>										
								<?php } ?>
							</div>
						<?php } ?>
					</div>
					<div class="clear_both"></div>
				</div></div>
	
			<?php } ?>
			
		</div>
		
		<?php if($this->pagination->getPagesLinks()){
			echo '<div class="pagination">';
				echo $this->pagination->getPagesLinks();
			echo '</div>';
		}?>	
		
	</div>	

</div>

<script type="text/javascript">
	function DJCatMatchModules(className){
		var maxHeight = 0;
		var divs = null;
		if (typeof(className) == 'string') {
			divs = document.id(document.body).getElements(className);
		} else {
			divs = className;
		}

		divs.setStyle('height', 'auto');
		
		if (divs.length > 1) {						
			divs.each(function(element) {
				//maxHeight = Math.max(maxHeight, parseInt(element.getStyle('height')));
				maxHeight = Math.max(maxHeight, parseInt(element.getSize().y));
			});
			
			divs.setStyle('height', maxHeight);
			
		}
}

window.addEvent('load', function(){
	DJCatMatchModules('.profile_item_in');
});

window.addEvent('resize', function(){
	DJCatMatchModules('.profile_item_in');
});	

</script>