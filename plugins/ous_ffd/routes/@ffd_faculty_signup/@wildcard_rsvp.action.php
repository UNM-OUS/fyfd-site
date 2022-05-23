<?php

use DigraphCMS\Context;
use DigraphCMS\HTTP\HttpError;
use DigraphCMS\HTTP\RedirectException;
use DigraphCMS\Session\Session;
use DigraphCMS\UI\Breadcrumb;
use DigraphCMS\UI\ButtonMenus\ButtonMenu;
use DigraphCMS\UI\ButtonMenus\ButtonMenuButton;
use DigraphCMS\UI\Format;
use DigraphCMS\UI\Notifications;
use DigraphCMS\URL\URL;
use DigraphCMS\Users\Permissions;
use DigraphCMS\Users\Users;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDStudentSignup;

/** @var FFDStudentSignup */
$page = Context::page();

$rsvp = $page->rsvp(Context::url()->action());
if (!$rsvp) return;

$isMine = in_array($rsvp['for'], Users::providerIDs(Session::user(), 'cas', 'netid'));
if (!($isMine || Permissions::inMetaGroups(['ffd_signup__edit', 'ffd_student__view']))) throw new HttpError(401);

printf('<h1>RSVP: %s %s</h1>', $rsvp['first_name'], $rsvp['last_name']);
Breadcrumb::setTopName(sprintf('%s %s', $rsvp['first_name'], $rsvp['last_name']));
Breadcrumb::parent(new URL('signup_list.html'));

if ($rsvp['cancelled']) {
    Notifications::printError(sprintf(
        'This RSVP was cancelled %s by %s',
        Format::date($rsvp['cancelled']),
        Users::user($rsvp['cancelled_by'])
    ));
}

printf('<p><strong>Signed up:</strong> %s by %s</p>', Format::date($rsvp['created']), Users::user($rsvp['created_by']));
if ($rsvp['created'] != $rsvp['updated']) printf('<p><strong>Updated:</strong> %s by %s</p>', Format::date($rsvp['updated']), Users::user($rsvp['updated_by']));

if (($isMine && $page->open()) || Permissions::inMetaGroup('ffd_signup__edit')) {
    $menu = new ButtonMenu(null, [new ButtonMenuButton('Edit RSVP', function () use ($rsvp) {
        throw new RedirectException(new URL('_edit_rsvp.html?uuid=' . $rsvp['uuid']));
    })]);
    if ($rsvp['cancelled']) {
        $menu->addButton(new ButtonMenuButton(
            'Un-cancel RSVP',
            function () use ($page, $rsvp) {
                $page->uncancelRSVP($rsvp['uuid']);
            },
            ['button--safe']
        ));
    } else {
        $menu->addButton(new ButtonMenuButton(
            'Cancel RSVP',
            function () use ($page, $rsvp) {
                $page->cancelRSVP($rsvp['uuid']);
            },
            ['button--danger']
        ));
    }
    echo $menu;
}
