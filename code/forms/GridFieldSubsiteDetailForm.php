<?php

namespace SilverStripe\Subsites\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;

class GridFieldSubsiteDetailForm extends GridFieldDetailForm
{

    protected $itemRequestClass = 'GridFieldSubsiteDetailForm_ItemRequest';

}
