<?php
namespace App\Models;

use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, NumberField, PrimaryKey, ForeignKey};

class PollAnswer extends Model{

  protected static $tableName = "poll_answers";

  #[PrimaryKey] protected $id;
  #[ForeignKey(Poll::class)] protected $poll;
  #[CharField] protected $title;
  #[NumberField] protected $votes;
}