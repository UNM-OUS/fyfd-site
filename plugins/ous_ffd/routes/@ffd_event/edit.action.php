<?php

use DigraphCMS\Context;
use DigraphCMS\HTML\Forms\Field;
use DigraphCMS\HTML\Forms\Fields\DateField;
use DigraphCMS\HTML\Forms\FormWrapper;
use DigraphCMS\HTTP\RedirectException;
use DigraphCMS\RichContent\RichContentField;
use DigraphCMS\Session\Cookies;
use DigraphCMS\UI\Notifications;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDEvent;

Cookies::required(['system', 'csrf']);

/** @var FFDEvent */
$page = Context::page();

$name = (new Field('Event name'))
    ->setDefault($page->name())
    ->setRequired(true)
    ->addTip('The name to be used when referring or linking to this event from elsewhere on the site.');

$date = (new DateField('Event date'))
    ->setDefault($page->date())
    ->setRequired(true);

$content = (new RichContentField('Body content'))
    ->setDefault($page->richContent('body'))
    ->setPageUuid(Context::pageUUID())
    ->setRequired(true);

$form = (new FormWrapper('edit-' . Context::pageUUID()))
    ->addChild($name)
    ->addChild($date)
    ->addChild($content)
    ->addCallback(function () use ($page, $name, $date, $content) {
        // update page
        $page->name($name->value());
        $page->setDate($date->value());
        $page->richContent('body', $content->value());
        $page->update();
        // redirect
        Notifications::flashConfirmation('Event updated: ' . $page->url()->html());
        throw new RedirectException($page->url());
    });
$form->button()->setText('Update event');

echo $form;
