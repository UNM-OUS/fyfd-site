<?php

namespace DigraphCMS_Plugins\unmous\ous_ffd\Forms;

use DigraphCMS\HTML\Forms\Fields\CheckboxField;
use DigraphCMS\HTML\Forms\FIELDSET;
use DigraphCMS\UI\Templates;

class WaiverAgreement extends FIELDSET
{
    public function __construct()
    {
        parent::__construct('Waiver agreement');
        $this->checkbox = (new CheckboxField('I have read and agree to the above'))
            ->setRequired(true);
        $this->addChild(Templates::render('ffd/student_waiver.php'));
        $this->addChild($this->checkbox);
        $this->addClass('waiver-agreement');
    }
}
