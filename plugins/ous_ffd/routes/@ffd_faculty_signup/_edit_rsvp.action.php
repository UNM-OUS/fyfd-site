<?php

use DigraphCMS\Context;
use DigraphCMS\HTML\Forms\Field;
use DigraphCMS\HTML\Forms\FormWrapper;
use DigraphCMS\HTTP\HttpError;
use DigraphCMS\HTTP\RedirectException;
use DigraphCMS\UI\Breadcrumb;
use DigraphCMS_Plugins\unmous\ous_digraph_module\Forms\AccommodationsField;
use DigraphCMS_Plugins\unmous\ous_ffd\FFDFacultySignup;
use DigraphCMS_Plugins\unmous\ous_regalia\Forms\RegaliaRequestField;

/** @var FFDFacultySignup */
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

$regalia = (new RegaliaRequestField('Academic regalia rental', $rsvp['for']))
    ->setDefault($rsvp['regalia_requested']);
$form->addChild($regalia);

$accommodations = (new AccommodationsField(null, true))
    ->setDefault($rsvp['accommodations']);
$form->addChild($accommodations);

if ($form->ready()) {
    $page->updateRSVP(
        $rsvp['uuid'],
        $firstName->value(),
        $lastName->value(),
        $regalia->value(),
        $accommodations->value()
    );
    throw new RedirectException(Context::page()->url($rsvp['uuid']));
}
echo $form;
