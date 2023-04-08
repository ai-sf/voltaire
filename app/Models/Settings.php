<?php

namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, NumberField, PrimaryKey};

class FantaCISFMember extends Model
{
    protected static $tableName = "settings";

    #[PrimaryKey] protected $id;
    #[CharField] protected $key;
    #[CharField] protected $value;
}
