<h1>New faculty RSVP</h1>
<?php

use DigraphCMS\Context;
use DigraphCMS\HTML\Forms\Field;
use DigraphCMS\HTML\Forms\FormWrapper;
use DigraphCMS\HTTP\HttpError;
use DigraphCMS\HTTP\RedirectException;
use DigraphCMS\Session\Session;
use DigraphCMS\UI\Notifications;
use DigraphCMS\URL\URL;
use DigraphCMS\Users\Permissions;
use DigraphCMS\Users\Users;
use DigraphCMS_Plugins\unmous\ous_digraph_module\Forms\AccommodationsField;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDFacultySignup;
use DigraphCMS_Plugins\unmous\ous_ffd\Forms\WaiverAgreement;
use DigraphCMS_Plugins\unmous\ous_regalia\Forms\RegaliaRequestField;

Permissions::requireAuth();

/** @var FFDFacultySignup */
$page = Context::page();

// get current user's NetID
$currentNetID = Users::providerIDs(Session::user(), 'cas', 'netid');
$currentNetID = reset($currentNetID);

// determine who signup is for
$for = $currentNetID;
if (Permissions::inMetaGroup('ffd_signup__edit')) $for = Context::arg('for') ?? $for;
if (!$for) throw new HttpError(400, 'Could not determine who the signup would be for. Make sure you are signed in using your NetID.');

// figure out if there's an existing signup for this user
if ($existing = $page->rsvpFor($for)) {
    Notifications::flashConfirmation("An existing RSVP was found for <code>$for</code>");
    throw new RedirectException($page->url($existing['uuid']));
}

Notifications::printNotice(sprintf(
    'A confirmation email will be sent to your main UNM email address as well as any additional <a href="%s" target="_lightbox">emails associated with your account</a>',
    new URL('/~user/email_addresses.html')
));

$form = new FormWrapper();
$form->button()->setText('Save RSVP');

$firstName = (new Field('First name'))
    ->setRequired(true);
$form->addChild($firstName);

$lastName = (new Field('Last name'))
    ->setRequired(true);
$form->addChild($lastName);

$regalia = (new RegaliaRequestField('Academic regalia rental', $for));
$form->addChild($regalia);

$accommodations = new AccommodationsField(null, true);
$form->addChild($accommodations);

$form->addChild(new WaiverAgreement);

if ($form->ready()) {
    $uuid = $page->newRSVP(
        $for,
        $firstName->value(),
        $lastName->value(),
        $regalia->value(),
        $accommodations->value()
    );
    throw new RedirectException(Context::page()->url($uuid));
}
echo $form;
