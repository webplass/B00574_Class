<?php
/**
 * @version $Id: jmspacer.php 38 2014-10-29 07:42:48Z michal $
 * @package JMFramework
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Michal Olczyk - michal.olczyk@design-joomla.eu
 *
 * JMFramework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JMFramework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JMFramework. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extended Spacer field
 */
class JFormFieldJmlabel extends JFormField
{
		protected $type = 'Jmlabel';
		protected function getInput()
		{
				return ' ';
		}

		/**
		 * Method to get the field label markup for a spacer.
		 * Use the label text or name from the XML element as the spacer or
		 * Use a hr="true" to automatically generate plain hr markup
		 *
		 */
		protected function getLabel()
		{

				$html = array();
				$class = $this->element['class'] ? (string) $this->element['class'] : '';
				$class .= ' jmlabel';

				$html[] = '<span class="' . $class . '">';

				$label = '';

				// Get the label text from the XML element, defaulting to the element name.
				$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
				$text = $this->translateLabel ? JText::_($text) : $text;

				// Build the class for the label.
				$class = !empty($this->description) ? 'hasTip' : '';

				// Add the opening label tag and main attributes attributes.
				$label .= '<label id="' . $this->id . '-lbl" class="' . $class . '"';

				// If a description is specified, use it to build a tooltip.
				if (!empty($this->description))
				{
						$label .= ' title="'
								. htmlspecialchars(
								trim($text, ':') . '::' . ($this->translateDescription ? JText::_($this->description) : $this->description),
								ENT_COMPAT, 'UTF-8'
						) . '"';
				}

				// Add the label text and closing tag.
				$label .= '>' . $text . '</label>';

				$html[] = $label;
				$html[] = '</span>';


				return implode('', $html);
		}

		/**
		 * Method to get the field title.
		 *
		 * @return  string  The field title.
		 *
		 * @since   11.1
		 */
		protected function getTitle()
		{
				return $this->getLabel();
		}
}
