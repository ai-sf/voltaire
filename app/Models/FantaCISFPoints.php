<?php

namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{ForeignKey, TextField, NumberField, PrimaryKey};

class FantaCISFPoints extends Model
{
    protected static $tableName = "fantacisf_points";

    #[PrimaryKey] protected $id;
    #[ForeignKey(FantaCISFMember::class)] protected $member;
    #[ForeignKey(FantaCISFBonus::class)] protected $bonus;
}
