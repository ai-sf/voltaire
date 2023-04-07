<?php

namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, NumberField, PrimaryKey};

class FantaCISFMember extends Model
{
    protected static $tableName = "fantacisf_teammember";

    #[PrimaryKey] protected $id;
    #[CharField] protected $name;
    #[CharField] protected $description;
    #[CharField] protected $photo;
    #[NumberField] protected $role;
}
