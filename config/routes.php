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
  "admin/polls/results/<int:id>" => [AdminController::class, "pollResults"],
  "admin/polls/graph/<int:id>" => [AdminController::class, "pollGraph"],

  "admin/users" => [AdminController::class, "usersList"],
  "admin/users/new" => [AdminController::class, "newUser"],
  "admin/users/save" => [AdminController::class, "saveUser"],
  "admin/users/activate/<int:id>?status=<int:status>" => [AdminController::class, "activateUser"],
  "admin/users/activate/<int:id>" => [AdminController::class, "activateUser"],
  "admin/users/sendMail/<int:id>" => [AdminController::class, "sendMail"],
  "admin/users/delete/<int:id>" => [AdminController::class, "deleteUser"],
  "admin/users/edit/<int:id>" =>  [AdminController::class, "editUser"],
  "admin/users/batchAction" => [AdminController::class, "batchAction"],
  "admin/users/search" => [AdminController::class, "userSearch"],
  "admin/users/batchUpload" => [AdminController::class, "userBatchUpload"],
  "admin/loginEmail" => [AdminController::class, "loginEmailPreview"],


  "fantacisf" => [FantaCISFController::class, "index"],
  "fantacisf/toggle/<int:id>" => [FantaCISFController::class, "toggle"],
  "fantacisf/saveteamname" => [FantaCISFController::class, "saveName"]

];
