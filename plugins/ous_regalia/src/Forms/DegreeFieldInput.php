<?php

namespace DigraphCMS_Plugins\unmous\ous_regalia\Forms;

use DigraphCMS\HTML\Forms\Fields\Autocomplete\AutocompleteInput;
use DigraphCMS\URL\URL;
use DigraphCMS_Plugins\unmous\ous_regalia\Regalia;

class DegreeFieldInput extends AutocompleteInput
{
    public function __construct(string $id = null)
    {
        parent::__construct(
            $id,
            new URL('/~api/v1/autocomplete/regalia-field.php'),
            function ($value) {
                $field = Regalia::fields()
                    ->where('field.id = ?', [$value])
                    ->fetch();
                if (!$field) return null;
                else return [
                    'html' => sprintf('<div class="label">%s</div>', $field['label']),
                    'value' => $field['id']
                ];
            }
        );
    }
}
