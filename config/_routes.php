<?php

use App\Controllers\AdminController;
use App\Controllers\FantaCISFController;
use App\Controllers\SiteController;

return [
  "" => [SiteController::class, "index"],
  "login" => [SiteController::class, "login"],
  "logout" => [SiteController::class, "logout"],

  "polls/<int:id>" => [SiteController::class, "poll"],
  "polls/vote" => [SiteController::class, "pollVote"],

  "admin" => [AdminController::class, "index"],
  "admin/polls" => [AdminController::class, "pollsList"],
  "admin/polls/edit/<int:id>" => [AdminController::class, "editPoll"],
  "admin/polls/new" => [AdminController::class, "newPoll"],
  "admin/polls/save" => [AdminController::class, "savePoll"],
  "admin/polls/activate" => [AdminController::class, "activatePoll"],
  "admin/polls/delete" => [AdminController::class, "deletePoll"],
  "admin/polls/showResults" => [AdminController::class, "showResults"],
  "admin/polls/toggleProject" => [AdminController::class, "toggleProject"],
  "admin/polls/results/<int:id>" => [AdminController::class, "pollResults"],
  "admin/polls/graph/<int:id>" => [AdminController::class, "pollGraph"],

  "admin/projector" => [AdminController::class, "projector"],
  "admin/projector/getProjectorPolls" => [AdminController::class, "getProjectorPolls"],

  "admin/users" => [AdminController::class, "usersList"],
  "admin/users/new" => [AdminController::class, "newUser"],
  "admin/users/save" => [AdminController::class, "saveUser"],
  "admin/users/activate/<int:id>?status=<int:status>" => [AdminController::class, "activateUser"],
  "admin/users/activate/<int:id>" => [AdminController::class, "activateUser"],
  "admin/users/sendMail/<int:id>" => [AdminController::class, "sendMail"],
  "admin/users/delete/<int:id>" => [AdminController::class, "deleteUser"],
  "admin/users/edit/<int:id>" =>  [AdminController::class, "editUser"],
  "admin/users/batchAction" => [AdminController::class, "batchAction"],
  "admin/users/toggleOnline/<int:id>" => [AdminController::class, "toggleOnline"],
  "admin/users/toggleOnline/<int:id>?online=<int:online>" => [AdminController::class, "toggleOnline"],
  "admin/users/search" => [AdminController::class, "userSearch"],
  "admin/users/batchUpload" => [AdminController::class, "userBatchUpload"],
  "admin/loginEmail" => [AdminController::class, "loginEmailPreview"],


  "admin/fantacisf/teams" => [AdminController::class, "fantacisfTeams"],
  "admin/fantacisf/bonuses" => [AdminController::class, "fantacisfBonuses"],
  "admin/fantacisf/bonusesMember/<int:id>" => [AdminController::class, "fantacisfBonusesMember"],
  "admin/fantacisf/setBonus/<int:member_id>/<int:bonus_id>" => [AdminController::class, "setBonus"],
  "admin/fantacisf/removeBonus/<int:member_id>/<int:bonus_id>" => [AdminController::class, "removeBonus"],
  "admin/fantacisf/startGame" => [AdminController::class, "startGame"],
    "admin/fantacisf/showPoints" => [AdminController::class, "showPoints"],

  "fantacisf" => [FantaCISFController::class, "index"],
  "fantacisf/toggle/<int:id>" => [FantaCISFController::class, "toggle"],
  "fantacisf/saveteamname" => [FantaCISFController::class, "saveName"],
  "fantacisf/league" => [FantaCISFController::class, "league"],
  "fantacisf/myteam/<int:update>" => [FantaCISFController::class, "showTeam"],
  "fantacisf/bonusmalus" => [FantaCISFController::class, "bonusMalus"],
  "fantacisf/bonusesMember/<int:id>" => [FantaCISFController::class, "fantacisfBonusesMember"]

];
