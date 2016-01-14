<?php

//================================================
// Create Form
//================================================
$form = new form_too();
$form->attr('class', 'form-horizontal');

//---------------------------------
// Control Group #1
//---------------------------------
$form->start_div(array('class' => 'control-group'));
$form->add_label('Email', array('class' => "control-label", 'for' => "inputEmail"));
$form->start_div(array('class' => 'controls'));

$txt_userid = new textbox('email', '');
$txt_userid->attrs(array(
	'placeholder' => 'Email',
	'id' => 'inputEmail'
));
$form->add_element($txt_userid);

$form->end_div();
$form->end_div();

//---------------------------------
// Control Group #2
//---------------------------------
$form->start_div(array('class' => 'control-group'));
$form->add_label('Password', array('class' => "control-label", 'for' => "inputPassword"));
$form->start_div(array('class' => 'controls'));

$txt_pass = new secret('password', '');
$txt_pass->attrs(array(
	'placeholder' => 'Password',
	'id' => 'inputPassword'
));
$form->add_element($txt_pass);

$form->end_div();
$form->end_div();

//---------------------------------
// Control Group #3
//---------------------------------
$form->start_div(array('class' => 'control-group'));
$form->start_div(array('class' => 'controls'));

$form->add_label(input(array('type' => 'checkbox')) . 'Remember me', array('class' => "checkbox"));
$form->add_element(button('Sign in', array('type' => "submit", 'class' => "btn")));

$form->end_div();
$form->end_div();



//================================================
// Render Form
//================================================
$form->render();

