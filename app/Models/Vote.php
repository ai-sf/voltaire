<?php
namespace App\Models;

use JetBrains\PhpStorm\Deprecated;
use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, NumberField, PrimaryKey, ForeignKey};

class Vote extends Model{

  protected static $tableName = "votes";

  #[PrimaryKey] protected $id;
  #[ForeignKey(Poll::class)] protected $poll;
  #[ForeignKey(User::class)] protected $user;
}