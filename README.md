form-generator-for-codeigniter
==============================

Generates a form dynamically based on passed in configuration instead of using a view for the form.  More of a proof of concept than a fully completed work.

after adding the library to your function, you can pass the following configuration file options to the init() function:

$config = array(
		'form_title' => 'Title text used in form',
		'form_id' => 'form', // id tag for form
		'form_action' => '/form', // URL for form to submit to
		'form_class' => 'form_class', // class(es) to include on form
		'hidden' => array('name' => 'value'), // array of field names and values for hidden fields
		'multipart' => FALSE,  // is this a multipart form
		'submit_button' => 'Submit', // name to use on submit button
		'after_button' => ' or <a href="/">Cancel</a>', // text to place after submit button (if any)
		'fields' => array('name' => 'field_name', 'type' => 'text', 'id' => 'field_id', // array of name/type/id info where 'type' can be any input type.  Alternately, can be just an array of single strings that are field names
	);
	
	this is used:
	$this->form->inig($config);
	
	See the documentation of each function in the library file for more details.