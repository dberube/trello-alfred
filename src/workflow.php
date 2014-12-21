<?php

require_once ( 'lib/bootstrap.php' );

$TrelloWorkflow = new TrelloWorkflow();
$TrelloWorkflow->make( $argv );