<?php

namespace App\Controllers;

use Lepton\Base\Application;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Controller\BaseController;
use Liquid\{Liquid, Template};

use App\Models\{User, FantaCISFMember, FantaCISFTeam, FantaCISFPoints};
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
                    "icon" => "house-door-fill",
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
        return $this->render("FantaCISF/index");
    }


    #[LoginRequired(1)]
    public function toggle($id)
    {
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


    #[LoginRequired(1)]
    public function saveName()
    {
        $user = (new UserAuthenticator())->getLoggedUser();
        $user->fantacisf_team = $_POST["team_name"];
        $user->save();

        return $this->showTeam(1);
    }



    #[LoginRequired(1)]
    public function showTeam($update){
        $user = (new UserAuthenticator())->getLoggedUser();
        $membriEC = FantaCISFMember::filter(role: 1);
        $membriLC = FantaCISFMember::filter(role: 2);
        $membriOC = FantaCISFMember::filter(role: 3);
        $mymembers = FantaCISFTeam::filter(user: $user);
        $selected = array();
        foreach($mymembers as $member) {
            $selected[] = $member->teamMember->id;
        }

        $data = [ "EC" => $membriEC,
        "LC" => $membriLC,
        "OC" => $membriOC,
        "selected" => $selected,
        "user" => $user];

        $headers = array();

        if($update == 1){
            $data["show_toast"] = true;
            $data["is_update"] = true;
            $headers["HX-Trigger"] = "showToast";
        }
        return $this->render("FantaCISF/myteam", $data, headers: $headers);
    }


    private function cmp($a, $b){
        return $a["points"] < $b["points"];
    }

    #[LoginRequired(1)]
    public function league()
    {
        $users = User::filter(fantacisf_team__neq: "");
        $standings = array();

        foreach($users as $user){
            $standings[] = [
                "id" => $user->id,
                "name" => $user->name." ".$user->surname,
                "team_name" => $user->fantacisf_team,
                "points" => $this->computePointsUser($user),
                "team" => FantaCISFTeam::filter(user: $user)->do()
            ];
        }
        usort($standings, array($this, "cmp"));
        return $this->render("FantaCISF/league", ["users" => $standings,
        "num_teams" => $users->count()]);
    }


    #[LoginRequired(1)]
    private function computePointsUser($user){
        $team = FantaCISFTeam::filter(user: $user);
        $points = 0;
        foreach($team as $member){
            $points += $this->computePointsMember($member->teamMember);
        }
        return $points;

    }


    #[LoginRequired(1)]
    private function computePointsMember($member){
        $memberbonus = FantaCISFPoints::filter(member: $member);
        $points = 0;
        foreach($memberbonus as $bonus){
            $points += $bonus->bonus->points;
        }
        return $points;

    }
}
