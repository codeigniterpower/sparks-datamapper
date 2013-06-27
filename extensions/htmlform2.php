<?php

/**
 * A custom version of DMZ_HTMLForm with some additions
 * - An automatic "None" or "Please select" option when drawing a dropdown list
 */

require('htmlform.php');

class DMZ_HTMLForm2 extends DMZ_HTMLForm {

	/**
	 * Render a single field.  Can be used to chain together multiple fields in a column.
	 *
	 * @param object $object The DataMapper Object to use.
	 * @param string $field The field to render.
	 * @param string $type  The type of field to render.
	 * @param array  $options  Various options to modify the output.
	 * @return Rendered String.
	 */
	function render_field($object, $field, $type = NULL, $options = NULL)
	{
		$value = '';

		if(array_key_exists($field, $object->has_one) || array_key_exists($field, $object->has_many))
		{
			// Create a relationship field
			$one = array_key_exists($field, $object->has_one);

			// attempt to look up the current value(s)
			if( ! isset($options['value']))
			{
				if($this->CI->input->post($field))
				{
					$value = $this->CI->input->post($field);
				}
				else
				{
					// load the related object(s)
					$sel = $object->{$field}->select('id')->get();
					if($one)
					{
						// only a single value is allowed
						$value = $sel->id;
					}
					else
					{
						// save what might be multiple values
						$value = array();
						foreach($sel as $s)
						{
							$value[] = $s->id;
						}
					}
				}

			}
			else
			{
				// value was already set in the options
				$value = $options['value'];
				unset($options['value']);
			}

			// Attempt to get a list of possible values
			if( ! isset($options['list']) || is_object($options['list']))
			{
				if( ! isset($options['list']))
				{
					// look up all of the related values
					$c = get_class($object->{$field});
					$total_items = new $c;
					// See if the custom method is defined
					if(method_exists($total_items, 'get_htmlform_list'))
					{
						// Get customized list
						$total_items->get_htmlform_list($object, $field);
					}
					else
					{
						// Get all items
						$total_items->get_iterated();
					}
				}
				else
				{
					// process a passed-in DataMapper object
					$total_items = $options['list'];
				}
				$list = array();
				foreach($total_items as $item)
				{
					// use the __toString value of the item for the label
					$list[$item->id] = (string)$item;
				}
				$options['list'] = $list;
			}

			// By if there can be multiple items, use a dropdown for large lists,
			// and a set of checkboxes for a small one.
			if($one || count($options['list']) > 6)
			{

				/* 7.6 */
				
				$required = $this->_get_validation_rule($object, $field, 'required');
				if ($one && (!$object->exists() || !$required)) {

					// 7.6: This doesn't work, as "None" appears at the end of the list:
					// $options['list'][0] = "None";					
					// Instead:				
					
					// 20121105: allow the prompt to be customised
					$prompt = false;
					if (isset($options['prompt'])) { // Note that we can set $prompt to false to remove the prompt altogether
						if ($options['prompt']) {
							$prompt = $options['prompt'];
						}
					}
					else {
						$prompt = 'None';
					}
					if ($prompt) {
						$options['list'] = array(0=>$prompt) + $options['list'];
					}
					
				}				

				$default_type = 'dropdown';
				if( ! $one && ! isset($options['size']))
				{
					// limit to no more than 8 items high.
					$options['size'] = min(count($options['list']), 8);
				}
			}
			else
			{
				$default_type = 'checkbox';
			}
		}
		else
		{
			// attempt to look up the current value(s)
			if( ! isset($options['value']))
			{
				if($this->CI->input->post($field))
				{
					$value = $this->CI->input->post($field);
					// clear default if set
					unset($options['default_value']);
				}
				else
				{
					if(isset($options['default_value']))
					{
						$value = $options['default_value'];
						unset($options['default_value']);
					}
					else
					{
						// the field IS the value.
						$value = $object->{$field};
					}
				}

			}
			else
			{
				// value was already set in the options
				$value = $options['value'];
				unset($options['value']);
			}
			// default to text
			$default_type = ($field == 'id') ? 'hidden' : 'text';

			// determine default attributes
			$a = array();
			// such as the size of the field.
			$max = $this->_get_validation_rule($object, $field, 'max_length');
			if($max === FALSE)
			{
				$max = $this->_get_validation_rule($object, $field, 'exact_length');
			}
			if($max !== FALSE)
			{
				$a['maxlength'] = $max;
				$a['size'] = min($max, 30);
			}
			$list = $this->_get_validation_info($object, $field, 'values', FALSE);
			if($list !== FALSE)
			{
				$a['list'] = $list;
			}
			$options = $options + $a;
			$extra_class = array();

			// Add any of the known rules as classes (for JS processing)
			foreach($this->auto_rule_classes as $rule => $c)
			{
				if($this->_get_validation_rule($object, $field, $rule) !== FALSE)
				{
					$extra_class[] = $c;
				}
			}

			// add or set the class on the field.
			if( ! empty($extra_class))
			{
				$extra_class = implode(' ', $extra_class);
				if(isset($options['class']))
				{
					$options['class'] .= ' ' . $extra_class;
				}
				else
				{
					$options['class'] = $extra_class;
				}
			}
		}

		// determine the renderer type
		$type = $this->_get_type($object, $field, $type);
		if(empty($type))
		{
			$type = $default_type;
		}

		// attempt to find the renderer function
		if(method_exists($this, '_input_' . $type))
		{
			return $this->{'_input_' . $type}($object, $field, $value, $options);
		}
		else if(function_exists('input_' . $type))
		{
			return call_user_func('input_' . $type, $object, $field, $value, $options);
		}
		else
		{
			log_message('error', 'FormMaker: Unable to find a renderer for '.$type);
			return '<span style="color: Maroon; background-color: White; font-weight: bold">FormMaker: UNABLE TO FIND A RENDERER FOR '.$type.'</span>';
		}

	}
}
