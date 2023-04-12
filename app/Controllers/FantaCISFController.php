<?php

namespace App\Controllers;

use Lepton\Base\Application;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Controller\BaseController;
use Liquid\{Liquid, Template};

use App\Models\{User, FantaCISFMember, FantaCISFBonus, FantaCISFTeam, FantaCISFPoints, FantaCISFSettings};
use Lepton\Authenticator\LoginRequired;
use Lepton\Http\HttpResponse;

class FantaCISFController extends BaseController
{
    public string $baseLink = "";
    protected array $default_parameters = [
            "nav" => [
                [
                    "title" => "Votazioni",
                    "link" => "",
                    "icon" => "bar-chart-line-fill",
                    "min_level" => 1
                ],
                [
                    "title" => "FantaCISF",
                    "link" => "fantacisf",
                    "icon" => "trophy-fill",
                    "min_level" => 1,
                    "subnav" => [
                        [
                            "title" => "La mia squadra",
                            "link" => "fantacisf"
                        ],
                        [
                            "title" => "Lega Fantacisf",
                            "link" => "fantacisf/league"
                        ],
                        [
                            "title" => "Bonus e malus",
                            "link" => "fantacisf/bonusmalus"
                        ]

                    ]
                ],
                [
                    "title" => "Admin",
                    "link" => "admin",
                    "icon" => "tools",
                    "min_level" => 2
                ]



        ]
    ];



    #[LoginRequired(level: 1)]
    public function index()
    {
        $setting = FantaCISFSettings::get(name: "has_started");
        return $this->render("FantaCISF/index", ["can_edit" => 1-$setting->value]);
    }


    #[LoginRequired(1)]
    public function toggle($id)
    {
        $setting = FantaCISFSettings::get(name: "has_started");
        if($setting->value == 0) {
            $user = (new UserAuthenticator())->getLoggedUser();
            $member = FantaCISFMember::get($id);
            $price = 0;
            switch($member->role) {
                case 1:
                    $price = 20;
                    break;
                case 2:
                    $price = 10;
                    break;
                case 3:
                    $price = 6;
                    break;
                default:
                    $price = 0;
            }
            $exists = FantaCISFTeam::filter(user: $user, teamMember: $member)->count();
            $team_number = FantaCISFTeam::filter(user: $user)->count();
            if($exists) {
                FantaCISFTeam::get(user: $user, teamMember: $member)->delete();
                echo "cancello";
                $user->fantacisf_budget += $price;
                $user->save();
            } else {
                if(($user->fantacisf_budget >= $price) && ($team_number < 5)) {
                    $teamAssociation = FantaCISFTeam::new(user: $user, teamMember: $member);
                    $teamAssociation->save();
                    $user->fantacisf_budget -= $price;
                    $user->save();
                } else {
                    exit;
                }

            }
            return new HttpResponse(200);
        }
        return new HttpResponse(200);

    }


    #[LoginRequired(1)]
    public function saveName()
    {
        $setting = FantaCISFSettings::get(name: "has_started");
        if($setting->value == 0) {
            $user = (new UserAuthenticator())->getLoggedUser();
            $user->fantacisf_team = $_POST["team_name"];
            $user->save();

            return $this->showTeam(1);
        } else {
            return new HttpResponse(201, body: "");
        }
    }



    #[LoginRequired(1)]
    public function showTeam($update)
    {
        $user = (new UserAuthenticator())->getLoggedUser();
        $membriEC = FantaCISFMember::filter(role: 1);
        $membriLC = FantaCISFMember::filter(role: 2);
        $membriOC = FantaCISFMember::filter(role: 3);
        $mymembers = FantaCISFTeam::filter(user: $user);
        $selected = array();
        foreach($mymembers as $member) {
            $selected[] = $member->teamMember->id;
        }

        $setting = FantaCISFSettings::get(name: "has_started");

        $data = [ "EC" => $membriEC,
        "LC" => $membriLC,
        "OC" => $membriOC,
        "selected" => $selected,
        "user" => $user,
        "can_edit" => 1-$setting->value];

        $headers = array();

        if($update == 1) {
            $data["show_toast"] = true;
            $data["is_update"] = true;
            $headers["HX-Trigger"] = "showToast";
        }
        return $this->render("FantaCISF/myteam", $data, headers: $headers);
    }


    public static function cmp($a, $b)
    {
        return $a["points"] < $b["points"];
    }

    #[LoginRequired(1)]
    public function league()
    {
        $users = User::filter(fantacisf_team__neq: "");
        $standings = array();

        foreach($users as $user) {
            $points = $this->computePointsUser($user);
            $standings[] = [
                "id" => $user->id,
                "name" => $user->name." ".$user->surname,
                "team_name" => $user->fantacisf_team,
                "points" => $points,
                "team" => FantaCISFTeam::filter(user: $user)->do(),
                "position" => 0
            ];

        }

        $last_points = -1;
        $last_position = 0;

        usort($standings, array(self::class, "cmp"));

        foreach($standings as $key => &$standing) {
            if($key == 0) {
                $standing["position"] = 1;
            } elseif($last_points > $standing["points"]) {
                $standing["position"] = $last_position + 1;
            } else {
                $standing["position"] = $last_position;
            }


            $last_points = $standing["points"];
            $last_position = $standing["position"];
        }

        $setting = FantaCISFSettings::get(name:"has_started");
        return $this->render("FantaCISF/league", ["users" => $standings,
        "num_teams" => $users->count(), "has_started" => $setting->value]);
    }


    #[LoginRequired(1)]
    public function computePointsUser($user)
    {
        $team = FantaCISFTeam::filter(user: $user);
        $points = 0;
        foreach($team as $member) {
            $points += $this->computePointsMember($member->teamMember);
        }
        return $points;

    }


    #[LoginRequired(1)]
    public function computePointsMember($member)
    {
        $memberbonus = FantaCISFPoints::filter(member: $member);
        $points = 0;
        foreach($memberbonus as $bonus) {
            $points += $bonus->bonus->points*$bonus->multiplier;
        }
        return $points;
    }

    #[LoginRequired(1)]
    public function bonusMalus()
    {
        $members = FantaCISFMember::all()->order_by("name");
        $members_array = array();
        foreach($members as $member) {
            $bonuses = FantaCISFPoints::filter(member: $member);
            $points = 0;
            foreach($bonuses as $bonus) {
                $points += $bonus->bonus->points;
            }
            $members_array[] = [
                    "id" => $member->id,
                    "name" => $member->name,
                    "photo" => $member->photo,
                    "description" => $member->description,
                    "points" => $points
                ];
        }
        usort($members_array, array(self::class, "cmp"));
        return $this->render("FantaCISF/bonusMalus", ["members" => $members_array]);
    }

    #[LoginRequired(1)]
    public function fantacisfBonusesMember($id)
    {
        $member = FantaCISFMember::get($id);
        $bonuses = FantaCISFBonus::all()->order_by("id");
        $memberBonuses = array();

        foreach($bonuses as $bonus) {
            $counts = FantaCISFPoints::filter(member: $member, bonus: $bonus)->count();
            if($counts > 0) {
                $memberBonuses[] = [
                    "id" => $bonus->id,
                    "name" => $bonus->name,
                    "points" => $bonus->points,
                    "times" => $counts
                ];
            }
        }

        return $this->render("FantaCISF/memberBonuses", ["member" => $member, "bonuses" => $memberBonuses]);
    }

}
