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

if (Context::page()->studentSignup()) {
    Notifications::printError("This event already has a student signup window created");
    return;
}

// ensure we have a UUID in the parameters
if (!Context::arg('uuid')) {
    $url = Context::url();
    $url->arg('uuid', Digraph::uuid());
    throw new RedirectException($url);
}

// validate parameter UUID
if (!Digraph::validateUUID(Context::arg('uuid') ?? '')) {
    $url = Context::url();
    $url->arg('uuid', Digraph::uuid());
    throw new RedirectException($url);
}

// ensure parameter UUID doesn't already exist
if (Pages::exists(Context::arg('uuid'))) {
    $url = Context::url();
    $url->arg('uuid', Digraph::uuid());
    throw new RedirectException($url);
}

$name = (new Field('Event name'))
    ->setRequired(true)
    ->setDefault('Student signup')
    ->addTip('The name to be used when referring or linking to this event from elsewhere on the site.');

$start = (new DatetimeField('Start time'))
    ->setRequired(true);

$end = (new DateTimeField('End time'))
    ->setRequired(true);

$content = (new RichContentField('Body content'))
    ->setPageUuid(Context::arg('uuid'))
    ->setRequired(true);

// set defaults from the last one created
/** @var FFDStudentSignup */
$last = Pages::select()
    ->where("class = 'ffd_student_signup'")
    ->order("created DESC")
    ->limit(1)
    ->fetch();
if ($last) {
    Notifications::printConfirmation('Defaults set to match the most recently-created signup window');
    $name->setDefault($last->name());
    $content->setDefault($last->richContent('body')->source());
}

// handle form
$form = (new FormWrapper('add-' . Context::arg('uuid')))
    ->addChild($name)
    ->addChild($start)
    ->addChild($end)
    ->addChild($content)
    ->addCallback(function () use ($name, $start, $end, $content) {
        // insert page
        $page = new FFDStudentSignup();
        $page->setUUID(Context::arg('uuid'));
        $page->setStart($start->value());
        $page->setEnd($end->value());
        $page->name($name->value());
        $page->richContent('body', $content->value());
        // insert with parent link to current context page
        $page->insert(Context::page()->uuid());
        // redirect
        Notifications::flashConfirmation('Page created: ' . $page->url()->html());
        throw new RedirectException($page->url());
    });
$form->button()->setText('Create signup window');

echo $form;
