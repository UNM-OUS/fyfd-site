<?php

use DigraphCMS\Context;
use DigraphCMS\HTTP\HttpError;
use DigraphCMS\Session\Cookies;
use DigraphCMS_Plugins\unmous\ous_regalia\Regalia;

if (Context::arg('csrf') !== Cookies::csrfToken('autocomplete')) {
    throw new HttpError(400);
}

Context::response()->filename('response.json');

$fields = [];
// exact label matches
$query = Regalia::fields();
if ($phrase = trim(Context::arg('query'))) {
    $query->where('field.label = ?', $phrase);
}
$fields = $fields + $query->fetchAll();
// exact internal matches
$query = Regalia::fields();
if ($phrase = trim(Context::arg('query'))) {
    $query->where('field.label like ?', "%$phrase%");
}
$query->limit(20);
$fields = $fields + $query->fetchAll();
// fuzzier matches
$query = Regalia::fields();
foreach (explode(' ', Context::arg('query')) as $word) {
    $word = strtolower(trim($word));
    if ($word) {
        $query->where('field.label like ?', "%$word%");
    }
}
$query->limit(10);
$fields = $fields + $query->fetchAll();

echo json_encode(
    array_map(
        function (array $field) {
            return [
                'html' => sprintf('<div class="label">%s</div>', $field['label']),
                'value' => $field['id']
            ];
        },
        $fields
    )
);
