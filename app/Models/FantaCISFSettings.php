<?php

namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, NumberField, PrimaryKey};

class FantaCISFSettings extends Model
{
    protected static $tableName = "fantacisf_settings";

    #[PrimaryKey] protected $id;
    #[CharField] protected $name;
    #[NumberField] protected $value;
}
