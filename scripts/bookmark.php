<?php
chdir(dirname(__FILE__).'/..');
include('vendor/autoload.php');

Lithograph\Feed::poll();

#Lithograph\Bookmark::process(2);

