<?php
namespace App\Models;

use JetBrains\PhpStorm\Deprecated;
use Lepton\Boson\Model;
use Lepton\Boson\DataTypes\{CharField, TextField, PrimaryKey, DateTimeField, NumberField};

class Poll extends Model{

  protected static $tableName = "polls";

  #[PrimaryKey] protected $id;
  #[CharField] protected $title;
  #[TextField] protected $description;
  #[CharField] protected $access_code;
  #[NumberField] protected $active;
  #[NumberField] protected $show_results;
  #[NumberField] protected $project;
  #[DateTimeField] protected $timestamp;
}