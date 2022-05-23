<?php

use DigraphCMS\Content\Pages;
use DigraphCMS\Context;
use DigraphCMS\Digraph;
use DigraphCMS\HTML\Forms\Field;
use DigraphCMS\HTML\Forms\Fields\DatetimeField;
use DigraphCMS\HTML\Forms\FormWrapper;
use DigraphCMS\HTTP\RedirectException;
use DigraphCMS\RichContent\RichContentField;
use DigraphCMS\Session\Cookies;
use DigraphCMS\UI\Notifications;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDStudentSignup;

Cookies::required(['system', 'csrf']);

/** @var FFDStudentSignup */
$page = Context::page();

$name = (new Field('Event name'))
    ->setDefault($page->name())
    ->setRequired(true)
    ->addTip('The name to be used when referring or linking to this event from elsewhere on the site.');

$start = (new DatetimeField('Start time'))
    ->setDefault($page->start())
    ->setRequired(true);

$end = (new DateTimeField('End time'))
    ->setDefault($page->end())
    ->setRequired(true);

$content = (new RichContentField('Body content'))
    ->setDefault($page->richContent('body'))
    ->setPageUuid(Context::pageUUID())
    ->setRequired(true);

$form = (new FormWrapper('add-' . Context::arg('uuid')))
    ->addChild($name)
    ->addChild($start)
    ->addChild($end)
    ->addChild($content)
    ->addCallback(function () use ($page, $name, $start, $end, $content) {
        // update page
        $page->name($name->value());
        $page->setStart($start->value());
        $page->setEnd($end->value());
        $page->richContent('body', $content->value());
        $page->update();
        // redirect
        Notifications::flashConfirmation('Page updated: ' . $page->url()->html());
        throw new RedirectException($page->url());
    });
$form->button()->setText('Update signup window');

echo $form;
