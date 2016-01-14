<?php

//================================================
// Create Form
//================================================
$form = new form_too();

// Form Label
$form->label('Legend');

// Add Elements / Labels
$form->add_label('Label name');
$form->add_element(input(array('type' => 'text', 'placeholder' => "Type something...")));
$form->add_element(span('Example block-level help text here.', array('class' => "help-block")));
$form->add_label(input(array('type' => 'checkbox')) . 'Check me out', array('class' => "checkbox"));
$form->add_element(button('Submit', array('type' => "submit", 'class' => "btn")));

//================================================
// Render Form
//================================================
$form->render();

