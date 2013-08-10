<?php

//================================================
// Create Form
//================================================
$form = new form_too();

//---------------------------------------
// Form Label
//---------------------------------------
$form->label('Test Form');

//---------------------------------------
// Add Hidden Variables
//---------------------------------------
$form->add_hidden('test1', 1);
$form->add_hidden('test2', 'Bob', array('class' => 'test_class'));

//---------------------------------------
// Start Fieldset
//---------------------------------------
$form->start_fieldset('Test', array('class' => 'fs_class'), array('class' => 'leg_class'));

$form->add_label('Label name');
$form->add_element(input(array('type' => 'text', 'placeholder' => "Type something...")));

//---------------------------------------
// End Fieldset
//---------------------------------------
$form->end_fieldset();

$form->add_element(span('Example block-level help text here.', array('class' => "help-block")));
$form->add_label(input(array('type' => 'checkbox')) . 'Check me out', array('class' => "checkbox"));
$form->add_element(button('Submit', array('type' => "submit", 'class' => "btn")));

//---------------------------------------
// Triple Layered Divs
//---------------------------------------
$form->start_div(array('id' => 'div1'));
$form->start_div(array('id' => 'div2'));
$form->start_div(array('id' => 'div3'));
$form->add_label('Triple Layered Divs');
$form->end_div();
$form->end_div();
$form->end_div();


//---------------------------------------
// Passing Multiple Elements
//---------------------------------------
$form->add_element(array(
	'Test:',
	new ssa('test_ssa', array('Yes', 'No')),
	'Complete:',
	new checkbox('test_checkbox', 1, 0)
));

//================================================
// Render Form
//================================================
//$form->no_xsl();
$form->render();

?>