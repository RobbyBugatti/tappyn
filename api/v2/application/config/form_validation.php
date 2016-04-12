<?php defined("BASEPATH") or exit('No direct script access allowed');

$config = array();
$config['contest:create'] = array(
    array(
        'field' => 'different',
        'label' => "How Youre Different",
        'rules' => 'required'
    ),
    array(
        'field' => 'objective',
        'label' => "Objective",
        'rules' => 'required'
    ),
    array(
        'field' => 'platform',
        'label' => 'Format',
        'rules' => "Required",
    ),
    array(
        'field' => 'summary',
        'label' => "Summary",
        'rules' => "required"
    ),
    array(
        'field' => 'audeince',
        'label' => "Audience Description",
        'rules' => 'required'
    ),
    array(
        'field' => 'industry',
        'label' => "Interest",
        'rules' => 'required'
    ),
    array(
        'field' => 'display_type',
        'label' => "Display Type",
        'rules' => 'required'
    )
);
$config['auth:user_register'] = array(

);

$config['auth:company_register'] = array(

);
$config['auth:login'] = array(
    array(
        'field' => 'identity',
        'label' => "Identity",
        'rules' => 'required',
    ),
    array(
        'field' => "password",
        'label' => "Password",
        'rules' => 'required'
    )
);
