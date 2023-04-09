<?php

namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{TextField, NumberField, PrimaryKey};

class FantaCISFBonus extends Model
{
    protected static $tableName = "fantacisf_bonusmalus";

    #[PrimaryKey] protected $id;
    #[TextField] protected $name;
    #[NumberField] protected $points;
}
