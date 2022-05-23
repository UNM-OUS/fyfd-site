<h1>Fields of study</h1>
<?php

use DigraphCMS\UI\DataTables\ColumnHeader;
use DigraphCMS\UI\DataTables\QueryColumnHeader;
use DigraphCMS\UI\DataTables\QueryTable;
use DigraphCMS\UI\Toolbars\ToolbarLink;
use DigraphCMS\URL\URL;
use DigraphCMS_Plugins\unmous\ous_regalia\Regalia;

$query = Regalia::allFields();
$query->order('field.id DESC');

// display the table of results
$table = new QueryTable(
    $query,
    function (array $row) {
        $id = $row['jostens_id'];
        return [
            implode('', [
                (!$row['deprecated']
                    ? new ToolbarLink('Hide', 'hide', function () use ($id) {
                        Regalia::query()
                            ->update('field', ['deprecated' => true], $id)
                            ->execute();
                    })
                    : new ToolbarLink('Show', 'show', function () use ($id) {
                        Regalia::query()
                            ->update('field', ['deprecated' => false], $id)
                            ->execute();
                    }))
                    ->setAttribute('data-target', '_frame'),
                new ToolbarLink('Copy', 'copy', null, new URL('_add.html?jid=' . $row['jostens_id'])),
                new ToolbarLink('Edit', 'edit', null, new URL('_edit.html?id=' . $row['id']))
            ]),
            $row['label']
                . ($row['deprecated'] ? ' <strong>[HIDDEN]</strong>' : '')
                . ($row['field_deprecated'] ? ' <strong>[DEPRECATED]</strong>' : ''),
            $row['field_color']
        ];
    },
    [
        new ColumnHeader(''),
        new QueryColumnHeader('Label', 'label', $query),
        new ColumnHeader('Color'),
    ]
);

$table->enableDownload(
    'fields of study',
    function ($row) {
        return [
            $row['label']
                . ($row['deprecated'] ? ' <strong>[HIDDEN]</strong>' : '')
                . ($row['field_deprecated'] ? ' <strong>[DEPRECATED]</strong>' : ''),
            $row['field_color'],
            $row['field_name']
        ];
    },
    [
        'Label',
        'Color',
        'Name'
    ]
);

echo $table;
