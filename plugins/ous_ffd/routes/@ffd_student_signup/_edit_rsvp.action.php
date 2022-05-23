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
use DigraphCMS\UI\Breadcrumb;
use DigraphCMS_Plugins\unmous\ous_digraph_module\Forms\AccommodationsField;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDStudentSignup;

/** @var FFDStudentSignup */
$page = Context::page();

$rsvp = $page->rsvp(Context::arg('uuid'));
if (!$rsvp) throw new HttpError(400);

Breadcrumb::parents([
    $page->url('signup_list'),
    ($page->url(Context::arg('uuid')))->setName($rsvp['first_name'] . ' ' . $rsvp['last_name'])
]);

printf('<h1>Edit RSVP: %s %s</h1>', $rsvp['first_name'], $rsvp['last_name']);

Breadcrumb::setTopName('Edit RSVP');

$form = new FormWrapper();
$form->button()->setText('Save RSVP');

$firstName = (new Field('First name'))
    ->setDefault($rsvp['first_name'])
    ->setRequired(true);
$form->addChild($firstName);

$lastName = (new Field('Last name'))
    ->setDefault($rsvp['last_name'])
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
    ->setDefault($rsvp['shirt'])
    ->addTip('While we cannot guarantee that your size will be available, your answer here will help us order the correct number of each shirt size.');
$form->addChild($shirtSize);

$guestSection = new FIELDSET('Your guests');

$guests = (new Field('Expected number of family/guests', (new INPUT())->setAttribute('type', 'number')))
    ->setDefault($rsvp['guest_count'])
    ->addTip('Used to plan seating for guests, such as how much overflow space to prepare for guests.');
$guestSection->addChild($guests);

$guestEmail = (new Field('Family/guest email', new Email))
    ->setDefault($rsvp['guest_email'])
    ->addTip('Used to contact your guests directly about ticketing and attendance. You can leave this blank if you are not bringing anyone or would not like us to contact them.');
$guestSection->addChild($guestEmail);

$alumniParents = (new Field('Alumni parents or guardians'))
    ->setDefault($rsvp['alumni_parents'])
    ->addTip('If any of your parents or guardians are UNM alumni, you may enter them here and we will provide the information to the Alumni Association for their records.');
$guestSection->addChild($alumniParents);

$accommodations = (new AccommodationsField(null, true))
    ->setDefault($rsvp['accommodations']);
$form->addChild($accommodations);

$form->addChild($guestSection);

if ($form->ready()) {
    $page->updateRSVP(
        $rsvp['uuid'],
        $firstName->value(),
        $lastName->value(),
        $shirtSize->value(),
        $guests->value(),
        $guestEmail->value(),
        $alumniParents->value(),
        $accommodations->value()
    );
    throw new RedirectException(Context::page()->url($rsvp['uuid']));
}
echo $form;
