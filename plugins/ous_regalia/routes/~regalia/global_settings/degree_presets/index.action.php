<h1>Degree presets</h1>
<?php

use DigraphCMS\UI\DataTables\ColumnHeader;
use DigraphCMS\UI\DataTables\QueryColumnHeader;
use DigraphCMS\UI\DataTables\QueryTable;
use DigraphCMS\UI\Toolbars\ToolbarLink;
use DigraphCMS\URL\URL;
use DigraphCMS_Plugins\unmous\ous_regalia\Regalia;

$query = Regalia::allPresets();

$table = new QueryTable(
    $query,
    function (array $row): array {
        $id = $row['id'];
        return [
            implode('', [
                (!$row['deprecated']
                    ? new ToolbarLink('Hide', 'hide', function () use ($id) {
                        Regalia::query()
                            ->update('preset', ['deprecated' => true], $id)
                            ->execute();
                    })
                    : new ToolbarLink('Show', 'show', function () use ($id) {
                        Regalia::query()
                            ->update('preset', ['deprecated' => false], $id)
                            ->execute();
                    }))
                    ->setAttribute('data-target', '_frame'),
                new ToolbarLink('Edit', 'edit', null, new URL('_edit.html?id=' . $row['id']))
            ]),
            $row['weight'],
            $row['label']
                . ($row['deprecated'] ? ' <strong>[HIDDEN]</strong>' : '')
                . ($row['field_deprecated'] ? ' <strong>[FIELD DEPRECATED]</strong>' : '')
                . ($row['jostens_deprecated'] ? ' <strong>[JOSTENS FIELD DEPRECATED]</strong>' : ''),
            $row['level'],
            @$row['field_label'] ?? '<em>user entry</em>'
        ];
    },
    [
        new ColumnHeader(''),
        new QueryColumnHeader('Weight', 'weight', $query),
        new QueryColumnHeader('Label', 'preset.label', $query),
        new ColumnHeader('Level'),
        new ColumnHeader('Field')
    ]
);

$table->enableDownload(
    'degree presets',
    function (array $row) {
        return [
            $row['label']
                . ($row['deprecated'] ? ' <strong>[HIDDEN]</strong>' : '')
                . ($row['field_deprecated'] ? ' <strong>[FIELD DEPRECATED]</strong>' : '')
                . ($row['jostens_deprecated'] ? ' <strong>[JOSTENS FIELD DEPRECATED]</strong>' : ''),
            $row['level'],
            @$row['field_label'] ?? ''
        ];
    },
    [
        'Preset',
        'Level',
        'Field'
    ]
);

echo $table;
