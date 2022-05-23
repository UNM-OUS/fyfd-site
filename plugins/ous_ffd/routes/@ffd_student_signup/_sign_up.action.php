<h1>New student RSVP</h1>
<?php

use DigraphCMS\Context;
use DigraphCMS\HTML\Forms\Email;
use DigraphCMS\HTML\Forms\Field;
use DigraphCMS\HTML\Forms\FIELDSET;
use DigraphCMS\HTML\Forms\FormWrapper;
use DigraphCMS\HTML\Forms\INPUT;
use DigraphCMS\HTML\Forms\SELECT;
use DigraphCMS\HTTP\HttpError;
use DigraphCMS\HTTP\RedirectException;
use DigraphCMS\Session\Session;
use DigraphCMS\UI\Notifications;
use DigraphCMS\URL\URL;
use DigraphCMS\Users\Permissions;
use DigraphCMS\Users\Users;
use DigraphCMS_Plugins\unmous\ous_digraph_module\Forms\AccommodationsField;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDStudentSignup;
use DigraphCMS_Plugins\unmous\ous_ffd\Forms\WaiverAgreement;

Permissions::requireAuth();

/** @var FFDStudentSignup */
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

$shirtSize = (new Field('Preferred T-shirt size', new SELECT([
    'S' => 'Small',
    'M' => 'Medium',
    'L' => 'Large',
    'XL' => 'Extra Large',
    '2XL' => '2X Large',
    '3XL' => '3X Large'
])))
    ->setRequired(true)
    ->setDefault('L')
    ->addTip('While we cannot guarantee that your size will be available, your answer here will help us order the correct number of each shirt size.');
$form->addChild($shirtSize);

$guestSection = new FIELDSET('Your guests');

$guests = (new Field('Expected number of family/guests', (new INPUT())->setAttribute('type', 'number')))
    ->addTip('Used to plan seating for guests, such as how much overflow space to prepare for guests.');
$guestSection->addChild($guests);

$guestEmail = (new Field('Family/guest email', new Email))
    ->addTip('Used to contact your guests directly about ticketing and attendance. You can leave this blank if you are not bringing anyone or would not like us to contact them.');
$guestSection->addChild($guestEmail);

$alumniParents = (new Field('Alumni parents or guardians'))
    ->addTip('If any of your parents or guardians are UNM alumni, you may enter them here and we will provide the information to the Alumni Association for their records.');
$guestSection->addChild($alumniParents);

$accommodations = new AccommodationsField(null, true);
$form->addChild($accommodations);

$form->addChild($guestSection);
$form->addChild(new WaiverAgreement);

if ($form->ready()) {
    $uuid = $page->newRSVP(
        $for,
        $firstName->value(),
        $lastName->value(),
        $shirtSize->value(),
        $guests->value(),
        $guestEmail->value(),
        $alumniParents->value(),
        $accommodations->value()
    );
    throw new RedirectException(Context::page()->url($uuid));
}
echo $form;
