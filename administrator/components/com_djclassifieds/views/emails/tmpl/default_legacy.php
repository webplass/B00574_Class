<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage 	DJ Classifieds Component
* @copyright 	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license 		http://www.gnu.org/licenses GNU/GPL
* @author 		url: http://design-joomla.eu
* @author 		email contact@design-joomla.eu
* @developer 	Łukasz Ciastek - lukasz.ciastek@design-joomla.eu
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

$cid = JRequest::getVar('cid',0,'','int');
defined ('_JEXEC') or die('Restricted access');
/*$limit = JRequest::getVar('limit', 25, '', 'int');
$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
$ord_t = JRequest::getVar('ord_t', 'desc');
$order = JRequest::getVar('order');
if($ord_t=="desc"){
	$ord_t='asc';
}else{
	$ord_t='desc';
}*/

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');

$saveOrder	= $listOrder == 'p.ordering'; 
?>
<form action="index.php?option=com_djclassifieds&task=emails" method="post" name="adminForm">
		<emailset id="filter-bar">
			<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_DJCLASSIFIEDS_SEARCH_IN_NAME'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<?php /*?>
			<select name="filter_category" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>				
				<?php echo JHtml::_('select.options', $this->categories, 'id', 'name', $this->state->get('filter.category'));?>
			</select>
			<?php */?>
		</div>
	</emailset>
	<div class="clr"> </div>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="5%">
                    <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                </th>
                <th width="5%">
					<?php echo JHtml::_('grid.sort', JText::_('COM_DJCLASSIFIEDS_ID'), 'e.id', $listDirn, $listOrder); ?>
                </th>                
                <th width="20%">
					<?php echo JHtml::_('grid.sort', JText::_('COM_DJCLASSIFIEDS_LABEL'), 'e.label', $listDirn, $listOrder); ?>
                </th>                
        </thead>
        <?php $i=0; 
	foreach($this->emails as $i =>$e){
	?>
        <tr>
            <td>
               <?php echo JHtml::_('grid.id', $i, $e->id); ?>
            </td>
            <td>
                <?php echo $e->id; ?>
            </td>
            <td>
				<a href="<?php echo JRoute::_('index.php?option=com_djclassifieds&task=email.edit&id='.(int) $e->id); ?>">
					<?php echo JText::_($e->label); ?>
				</a>				 
            </td>			                     
        </tr>
        <?php  
		} ?>
    
    <tfoot>
        <td colspan="8">
            <?php echo $this->pagination->getListFooter(); ?>
        </td>
    </tfoot>
	</table>
	<input type="hidden" name="view" value="emails" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_djclassifieds" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php echo DJCFFOOTER; ?>