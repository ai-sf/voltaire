<?php

namespace App\Models;

use JetBrains\PhpStorm\Deprecated;
use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, DateTimeField, NumberField, PrimaryKey, ForeignKey};

class User extends Model
{
    protected static $tableName = "users";

    #[PrimaryKey] protected $id;
    #[CharField] protected $name;
    #[CharField] protected $surname;
    #[CharField(max_length:256)] protected $token;
    #[CharField] protected $email;
    #[NumberField] protected $votes;
    #[CharField] protected $hash;
    #[NumberField] protected $level;
    #[NumberField] protected $active;
    #[DateTimeField] protected $last_login;
    #[NumberField] protected $fantacisf_budget;
    #[CharField] protected $fantacisf_team;
    #[ForeignKey(FantaCISFMember::class)] protected $fantacisf_captain;
    #[NumberField] protected $online;

}
