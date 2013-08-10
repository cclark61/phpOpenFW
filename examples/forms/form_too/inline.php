<?php

//================================================
// Create Form
//================================================
$form = new form_too();
$form->attr('class', 'form-inline');

$txt_userid = new textbox('email', '');
$txt_userid->attrs(array(
	'placeholder' => 'Email',
	'class' => 'input-small'
));
$form->add_element($txt_userid);

$txt_pass = new secret('password', '');
$txt_pass->attrs(array(
	'placeholder' => 'Password',
	'class' => 'input-small'
));
$form->add_element($txt_pass);

$form->add_label(input(array('type' => 'checkbox')) . 'Remember me', array('class' => "checkbox"));

$form->add_element(button('Sign in', array('type' => "submit", 'class' => "btn")));

//================================================
// Render Form
//================================================
$form->render();

?>