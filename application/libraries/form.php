<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Dynamically build forms for display
 */
class Form {

	protected $CI; // codeigniter
	protected $fields = array();		// array of fields
										// [] = array('category' => 'field|raw', 'field' => array());
	protected $form_title = 'Form';
	protected $form_id = 'form';
	protected $form_action = '';
	protected $form_class = '';
	protected $hidden = array();
	protected $multipart = FALSE; // default to standard form
	protected $submit_button = 'Submit';
	protected $after_button = '';
	
	protected $rules = array(); // storage for validation rules

	function __construct() {
		$this->CI = & get_instance();
		$this->CI->load->config('form');
	} // __construct
	
	/**
	 * initializes variables using $config
	 * @param type $config 
	 */
	function init($config) {
		if (is_array($config)) {
			if (isset($config['form_title'])) {
				$this->form_title = $config['form_title'];
			}
			if (isset($config['fields'])) {
				$this->fields_from_array($config['fields']);
			}
			
			if(isset($config['form_id'])) {
				$this->form_id = $config['form_id'];
			}
			if(isset($config['form_action'])) {
				$this->form_action = $config['form_action'];
			}
			if(isset($config['form_class'])) {
				$this->form_class = $config['form_class'];
			}
			if(isset($config['hidden'])) {
				if (is_array($config['hidden'])) {
					$this->hidden = $config['hidden'];
				}
			}
			if (isset($config['multipart'])) {
				$this->multipart = $config['multipart'];
			}
			if (isset($config['submit_button'])) {
				$this->submit_button = $config['submit_button'];
			}
			if (isset($config['after_button'])) {
				$this->after_button = $config['after_button'];
			}

		}
	} // init
	
	/**
	 * sets the form title
	 * @param type $title 
	 */
	function set_form_title($title = '') {
		if ($title <> '') {
			$this->form_title = $title;
		}
	}
	/**
	 * generates HTML for a field
	 * @param type $field 
	 */
	function html_from_field($field) {
		
		$value_format = 'value="%s"'; // since select and textarea use different
		switch ($field['type']) {
				case 'text':
					$format = '<input type="%1$s" %2$s placeholder="%3$s" id="%4$s" name="%5$s" %6$s %7$s />'; // %7$s is size/maxlength strings if set
					break;
				case 'textarea':
					$format = '<%1$s placeholder="%3$s" id="%4$s" name="%5$s" %7$s />%1$s</%1$s>'; // %7$s is rows/cols strings if set
					break;
				case 'select' :
					
					$format = '<select name="%5$s" id="%4$s" %6$s %7$s> %2$s </select> '; // %7%s is size string if set
					if (isset($field['options'])) {
						$op_format = '<option value="%s" %s>%s</option>';
						$op = '';
						
						if (isset($field['value'])) {
							if ($field['value'] <> '') {
								$field['selected'] = $field['value'];
							}
						}
						
						foreach ($field['options'] as $key => $value) {
							$selected = '';
							if (isset($field['selected'])) {
								
								if ($key == $field['selected']) {
									$selected = 'selected="selected"';
								} 
							}
							
							$op .= sprintf($op_format, $key, $selected, $value);
						}
						$field['value'] = $op;
					} else {
						$field['value'] = '';
					}
					$value_format = '%s';
					break;
				default:
					$format = '<input type="%1$s" %2$s placeholder="%3$s" id="%4$s" name="%5$s" class="%6$s" %7$s />'; // %7$s is size/maxlength strings if set
					break;
			}
			
			
			$data['name'] = $field['name'];
			
			$data['input'] = $field['extra_html']['pre_input'] . sprintf($format,
					$field['type'],
					$field['value'] <> '' ? sprintf($value_format, $field['value']) : '',
					$field['placeholder'],
					$field['id'],
					$field['name'],
					$field['class'] <> '' ? sprintf('class="%s"', $field['class']) : '',
					$field['size']
					) . 
					$field['extra_html']['post_input'];
			
			if (! isset($field['error'])) {
				$field['error'] = '';
			}
			$data['label'] = $field['extra_html']['pre_label'] . 
					$this->CI->load->view(
					$this->CI->config->item('label_wrapper'),
					array('name' => $field['id'], 'label' => $field['label'], 'error' => $field['error']),
					TRUE
					) . 
					$field['extra_html']['post_label'];
			if (isset($field['wrap'])) {
				return sprintf($field['wrap'], $this->CI->load->view($this->CI->config->item('row_wrapper'), $data, TRUE));
			} else {
				return $this->CI->load->view($this->CI->config->item('row_wrapper'), $data, TRUE);
			}
	} // html_from_field

	function html_from_raw($field) {
		return $this->CI->load->view(
					$this->CI->config->item('row_wrapper'),
					array(
						'label' => $this->CI->load->view(
								$this->CI->config->item('raw_label'),
								array('label' => $field['label']),
								TRUE),
						'input' => $field['input']),
					TRUE
				);
	}
	
	function render_attributes() {
		$attr = '';
		if ($this->form_id <> '') {
			$attr = sprintf('id="%s"', $this->form_id);
		}
		if ($this->form_class <> '') {
			$attr .= sprintf(' class="%s"', $this->form_class);
		}
		return $attr;
	} // render_attributes
	
	function render_button() {
		
		return $this->CI->load->view(
				$this->CI->config->item('submit_row'),
				array(
					'submit_button' => $this->CI->load->view(
							$this->CI->config->item('submit_button'),
							array('button_text' => $this->submit_button),
							TRUE
							),
					'after_button' => $this->after_button
					),
				TRUE);
	} // render_button
	
	
	/**
	 * renders the form.
	 * @param type $config configuration variables
	 * @param type $return_value if TRUE then return the value, otherwise just echo it
	 * @return type 
	 */
	function render($return_value = FALSE) {
		$this->CI->load->helper('form');
		$form_contents = '';

		foreach ($this->fields as $field) {
			switch ($field['category']) {
				case 'field':
					$form_contents .= $this->html_from_field($field['field']);
					break;
				case 'raw':
					$form_contents .= $this->html_from_raw($field['field']);
					break;
				case 'fieldset':
					$form_contents .= $this->CI->load->view('form/fieldset_split', $field, TRUE);
				default:
					break;
			}			
		}
		if ($this->multipart) {
			$open = form_open_multipart($this->form_action, $this->render_attributes(), $this->hidden);
		} else {
			$open = form_open($this->form_action, $this->render_attributes(), $this->hidden);
		}
		$full_form = 
				$open . 
				$this->CI->load->view(
					$this->CI->config->item('form_wrapper'),
					array('content' => $form_contents, 'form_title' => $this->form_title),
					TRUE
				) . 
				$this->render_button() . 
				form_close()
				;
		if ($return_value) {
			return $full_form;
		} else {
			echo $full_form;
		}
		
	} // render
	
	function fields_from_table($table_name) {
		
	} // fields_from_table
	
	function new_fieldset($legend) {
		$field['category'] = 'fieldset';
		$field['legend'] = $legend;
		$this->fields[] = $field;
	} // new_fieldset

	/**
	 * Add a single field to the form
	 * @param string $name name of the field
	 * @param string $type type of the field (i.e. text, select, email, etc)
	 * @param array $options array of options for the field - see _add_field for details
	 */
	function add_field( $name = 'field', $type = 'text', $options = array()) {
		$field = $options;
		$field['category'] = 'field';
		$field['name'] = $name;
		$field['type'] = $type;
		$this->_add_field($field);
	} // add_field
	
	/**
	 * adds raw html to the field array
	 * @param type $label
	 * @param type $field add
	 */
	function add_raw($label, $field) {
		$this->fields[] = array('category' => 'raw', 'field' => array('label' => $label, 'input' => $field));
		
	} // add_raw
	
	function validation_rules() {
		
		foreach ($this->fields as $field) {
			if ($field['category'] == 'field') {
				if ( ! isset($field['field']['rule'])) {
					$field['field']['rule'] = '';
				}
				$rules[] = array('field' => $field['field']['name'], 'label' => $field['field']['label'], 'rules' => $field['field']['rule']);
			}
		}
		return $rules;
	}
	
	function fields_from_array($data) {
		foreach ($data as $field) {
			if (! is_array($field)) {
				
				// just a field name
				$field = array(
					'name' => $field,
					'type' => 'text',
					'id' => $field,
					);
			}
			// more complex processing here
			$this->add_field($field);
		}
	} // fields_from_array
	
	/**
	 * adds a field.
	 * @param array $field array of data for a field
	 * name Name of the field,
	 * type html field type,
	 * class class string,
	 * id id string,
	 * size size of field,
	 * maxlength maxlength of field,
	 * rows rows of textarea,
	 * cols cols of textarea,
	 * placeholder placeholder text,
	 * label (name to put in the label),
	 * value value of field,
	 * error error message, 
	 * wrap (string, '%s', allows to wrap the field in some html),
	 * extra_html extra html to include after field,
	 * rule (form validation rule),
	 * options (array of value/display pairs for select (array(1 => 'one', 2 => 'two')),
	 * selected which options array member is selected (if any)
	 */
	function _add_field($field) {
		
		// TODO  -- render on add to fields, so fields is chunks of HTML?
		
		foreach ($field as $key => $value) {
			if (is_string($value)) {
				$field[$key] = trim($value);
			}
		}
		
		$the_field['name'] = isset($field['name']) ? $field['name'] : ''; // no name if not specified
		$the_field['type'] = isset($field['type']) ? $field['type'] : 'text';
		$the_field['class'] = isset($field['class']) ? $field['class'] : ''; // no class if not specified
		$the_field['id'] = isset($field['id']) ? $field['id'] : $the_field['name']; // default to same id as name
		$the_field['size'] = isset($field['size']) ? sprintf(' size="%s" ', $field['size']) : '';
		$the_field['size'] .= isset($field['maxlength']) ? sprintf(' maxlength="%s" ', $field['maxlength']) : '';
		$the_field['size'] .= isset($field['rows']) ? sprintf(' rows="%s" ', $field['rows']) : '';
		$the_field['size'] .= isset($field['cols']) ? sprintf(' cols="%s" ', $field['cols']) : '';
		$the_field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : $the_field['name'];
		$the_field['label'] = isset($field['label']) ? $field['label'] : $the_field['name'];
		$the_field['value'] = isset($field['value']) ? $field['value'] : '';
		$the_field['error'] = isset($field['error']) ? $field['error'] : '';
		$the_field['wrap'] = isset($field['wrap']) ? $field['wrap'] : '%s';
		if (isset($field['extra_html'])) {
			if (! isset($field['extra_html']['pre_label'])) { $field['extra_html']['pre_label'] = ''; }
			if (! isset($field['extra_html']['post_label'])) { $field['extra_html']['post_label'] = ''; }
			if (! isset($field['extra_html']['pre_input'])) { $field['extra_html']['pre_input'] = ''; }
			if (! isset($field['extra_html']['post_input'])) { $field['extra_html']['post_input'] = ''; }
			$the_field['extra_html'] = $field['extra_html'];
		} else {
			$the_field['extra_html'] = array('pre_label' => '', 'post_label' => '', 'pre_input' => '', 'post_input' => '');
		}
		
		if (isset($field['rule'])) {
			$the_field['rule'] = $field['rule'];
			
		}
		if (isset($field['options'])) {
			$the_field['options'] = $field['options'];
		}
		if (isset($field['selected'])) {
			$the_field['selected'] = $field['selected'];
		}
		$this->fields[] = array('category' => 'field', 'field' => $the_field);
	} // add_field
} // class Form 