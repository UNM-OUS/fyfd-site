<?php

use DigraphCMS\Context;
use DigraphCMS\DB\DB;
use DigraphCMS\HTML\Forms\Field;
use DigraphCMS\HTML\Forms\FormWrapper;
use DigraphCMS\HTML\Forms\SELECT;
use DigraphCMS\UI\DataTables\ColumnHeader;
use DigraphCMS\UI\DataTables\QueryColumnHeader;
use DigraphCMS\UI\DataTables\QueryTable;
use DigraphCMS\UI\Format;
use DigraphCMS\Users\Users;

$query = DB::query()->from('ffd_student_rsvp')
    ->where('page_uuid = ?', [Context::pageUUID()]);

echo "<div class='navigation-frame' id='rsvp-report-interface'>";

$form = new FormWrapper('q');
$form->token()->setCSRF(false);
$form->setAttribute('data-target', 'rsvp-report-interface');
$form->setMethod($form::METHOD_GET);

$cancellation = new Field('Cancellation status', new SELECT([
    'n' => 'Not cancelled',
    'y' => 'Cancelled',
    'e' => 'Either'
]));
$cancellation->input()->setID('cancellation');
$form->addChild($cancellation);

$accommodations = new Field('Accommodations', new SELECT([
    'e' => 'Either',
    'y' => 'Requested',
    'n' => 'Not requested',
]));
$accommodations->input()->setID('accommodations');
$form->addChild($accommodations);

$search = new Field('Search names');
$search->input()->setID('q');
$form->addChild($search);

// cancellation where statements
if ($cancellation->value(true) == 'n' || $cancellation->value(true) === null) $query->where('cancelled IS NULL');
elseif ($cancellation->value(true) == 'y') $query->where('cancelled IS NOT NULL');
// accommodations where statements
if ($accommodations->value(true) == 'n') $query->where('accommodations IS NULL');
elseif ($accommodations->value(true) == 'y') $query->where('accommodations IS NOT NULL');
// searches
$words = explode(' ', strtolower($search->value(true)));
$words = array_filter($words, function ($e) {
    return !!$e;
});
if ($words) {
    $params = [];
    $q = sprintf('(%s)', implode(' OR ', array_map(
        function ($word) use (&$params) {
            $params[] = "%$word%";
            $params[] = "%$word%";
            return 'first_name LIKE ? OR last_name LIKE ?';
        },
        $words
    )));
    $query->where("($q)", $params);
}
$form->button()->setText('Filter list');
echo $form;

$table = new QueryTable(
    $query,
    function ($row) {
        $row['accommodations'] = $row['accommodations']
            ? json_decode($row['accommodations'], true)
            : null;
        return [
            Context::page()->url($row['uuid'])->html(),
            $row['first_name'],
            $row['last_name'],
            Format::date($row['created']),
            Format::date($row['updated']),
            $row['cancelled']
                ? Format::date($row['cancelled']) . '<br>' . Users::user($row['cancelled_by'])
                : '',
            $row['accommodations']
                ? implode('<br>', $row['accommodations']['needs'])
                : '',
            $row['accommodations']
                ? $row['accommodations']['phone']
                : ''
        ];
    },
    [
        new ColumnHeader(''),
        new QueryColumnHeader('First name', 'first_name', $query),
        new QueryColumnHeader('Last name', 'last_name', $query),
        new QueryColumnHeader('Created', 'created', $query),
        new QueryColumnHeader('Updated', 'updated', $query),
        new ColumnHeader('Cancelled'),
        new ColumnHeader('Accommodations'),
        new ColumnHeader('Phone')
    ]
);
$table->enableDownload(
    'student signup list ' . date('Y-m-d'),
    function (array $row) {
        if ($row['accommodations']) $row['accommodations'] = json_decode($row['accommodations'], true);
        return [
            $row['first_name'],
            $row['last_name'],
            $row['for'],
            $row['for'] . '@unm.edu',
            $row['created'],
            $row['created_by'],
            $row['updated'],
            $row['updated_by'],
            $row['cancelled'] ? $row['cancelled'] : '',
            $row['cancelled'] ? $row['cancelled_by'] : '',
            $row['accommodations']
                ? implode(', ', $row['accommodations']['needs'])
                : '',
            $row['accommodations']
                ? $row['accommodations']['phone']
                : '',
            $row['accommodations']
                ? $row['accommodations']['extra']
                : ''

        ];
    },
    [
        'First name',
        'Last name',
        'NetID',
        'Email',
        'Created',
        'Created by',
        'Updated',
        'Updated by',
        'Cancelled',
        'Cancelled by',
        'Accommodations',
        'Phone',
        'Extra accommodations information'
    ]
);
echo $table;

echo "</div>";
