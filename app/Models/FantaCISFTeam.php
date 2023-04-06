<?php
namespace App\Models;

use JetBrains\PhpStorm\Deprecated;
use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{PrimaryKey, ForeignKey};

class FantaCISFTeam extends Model{

  protected static $tableName = "fantacisf_user_has_team";

  #[PrimaryKey] protected $id;
  #[ForeignKey(User::class)] protected $user;
  #[ForeignKey(FantaCISFMember::class)] protected $teamMember;
}