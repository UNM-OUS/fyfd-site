<?php

use DigraphCMS\Context;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDEvent;

/** @var FFDEvent */
$ffd = Context::page();

echo $ffd->richContent('body');

if ($student = $ffd->studentSignup()) echo $student->embedCard();
if ($faculty = $ffd->facultySignup()) echo $faculty->embedCard();
