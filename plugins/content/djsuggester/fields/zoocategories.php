<?php
/**
 * @package DJ-Suggester
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldZooCategories extends JFormField
{
	protected $type = 'ZooCategories';

	protected function getInput()
	{
		// load config
		require_once(JPATH_ROOT.'/administrator/components/com_zoo/config.php');
		
		$zoo = App::getInstance('zoo');
		
		$table	= $zoo->table->application;

		if( !is_array( $this->value ) )
		{
			$this->value	= array( $this->value );
		}
		
		ob_start();
		?>
		<select name="<?php echo $this->name;?>[]" multiple="multiple" style="width:220px;height:200px;" class="<?php echo $this->element['class'];?>">
		<?php $selected	= in_array( 'all' , $this->value ) ? ' selected="selected"' : ''; ?>
		<option value="all"<?php echo $selected;?>><?php echo JText::_('PLG_DJSUGGESTER_ZOO_ALL_APPLICATIONS'); ?></option>
		<?php		
		foreach ($table->all(array('order' => 'name')) as $application) {
		?>
			<optgroup label="<?php echo $application->name;?>"><?php
			// todo: whole application option <option value="app:$application->id"> All from [$application->name] </option>
			$cats = $application->getCategoryTree();
			foreach($cats as $cat) {
				if($cat->id == 0) continue; // don't include root
				//JFactory::getApplication()->enqueueMessage("<pre>".print_r($cat, true)."</pre>"); break;
				$selected	= in_array( $cat->id , $this->value ) ? ' selected="selected"' : '';
				?><option value="<?php echo $cat->id;?>"<?php echo $selected;?>><?php echo ($cat->parent ? '- ':'') . $cat->name;?></option><?php
			} ?>
			</optgroup><?php 
		}
		?>
		</select>
		<?php
		$html	= ob_get_contents();
		ob_end_clean();
		
		return $html;
	}

}
