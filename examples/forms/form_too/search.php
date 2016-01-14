<?php

//================================================
// Create Form
//================================================
$form = new form_too();
$form->attr('class', 'form-search');

$form->add_element(input(array('type' => 'text', 'placeholder' => "Search", 'class' => 'input-medium search-query')));
$form->add_element(button('Search', array('type' => "submit", 'class' => "btn")));

//================================================
// Render Form
//================================================
$form->render();

