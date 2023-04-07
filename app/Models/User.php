<?php

namespace App\Models;

use JetBrains\PhpStorm\Deprecated;
use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, DateTimeField, NumberField, PrimaryKey};

class User extends Model
{
    protected static $tableName = "users";

    #[PrimaryKey] protected $id;
    #[CharField] protected $name;
    #[CharField] protected $surname;
    #[CharField] protected $token;
    #[CharField] protected $email;
    #[NumberField] protected $votes;
    #[CharField] protected $hash;
    #[NumberField] protected $level;
    #[NumberField] protected $active;
    #[DateTimeField] protected $last_login;
    #[NumberField] protected $fantacisf_budget;
    #[CharField] protected $fantacisf_team;

}
