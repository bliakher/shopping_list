#!/usr/bin/php
<?php

require_once __DIR__ . '/templator.php';

$templator = new Templator();
$templator->loadTemplate(__DIR__ . '/items_table.tpl.html');
$templator->compileAndSave('/home/golubee/public_html/shopping_list/html/items_table.php');
