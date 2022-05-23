<?php

use DigraphCMS\Context;
use DigraphCMS\HTML\Forms\Field;
use DigraphCMS\HTML\Forms\FormWrapper;
use DigraphCMS\HTTP\RedirectException;
use DigraphCMS\Session\Session;
use DigraphCMS\UI\Format;
use DigraphCMS\UI\Notifications;
use DigraphCMS\URL\URL;
use DigraphCMS\Users\Permissions;
use DigraphCMS\Users\Users;
use DigraphCMS_Plugins\unmous\ous_digraph_module\Forms\EmailOrNetIDInput;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDStudentSignup;

/** @var FFDStudentSignup */
$page = Context::page();

// display informational stuff
echo $page->richContent('body');

// notification if user is bypassing closure
if (!$page->open() && Permissions::inMetaGroup('ffd_signup__edit')) {
    Notifications::printNotice("This signup window is not open to the general public, but you have special permissions to create signups anyway.");
}

if ($page->open() || Permissions::inMetaGroup('ffd_signup__edit')) {
    if (Session::user()) {
        // display signup form/button
        if (Permissions::inMetaGroup('ffd_signup__edit')) {
            $form = new FormWrapper();
            $form->button()->setText('Begin RSVP');
            $for = new Field('Faculty NetID or Email', new EmailOrNetIDInput());
            $for->addTip("Please use a NetID if available, as not all self-service features are available for RSVPs that are linked to emails instead of a NetIDs.");
            $form->addChild($for);
            if ($form->ready()) {
                throw new RedirectException(new URL('_sign_up.html?for='.$for->value()));
            }
            echo $form;
        } else {
            printf(
                '<a href="%s" class="button button--safe">Create new RSVP</a>',
                $page->url('_sign_up')
            );
        }
    } else {
        Notifications::printNotice("You must be logged in to use this form. " . Users::signinUrl(Context::url())->html());
    }
} elseif ($page->ended()) {
    Notifications::printWarning("This signup window ended " . Format::datetime($page->end()));
} elseif ($page->notStarted()) {
    Notifications::printWarning("This signup window doesn't open until " . Format::datetime($page->start()));
} else {
    Notifications::printError("This signup window is temporarily closed. Please check back later.");
}
