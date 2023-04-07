<?php

namespace App\Controllers;

use Lepton\Base\Application;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Controller\BaseController;
use Liquid\{Liquid, Template};

use App\Models\{User, FantaCISFMember, FantaCISFTeam};
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
                    "min_level" => 1
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

        $user = (new UserAuthenticator())->getLoggedUser();
        $membriEC = FantaCISFMember::filter(role: 1);
        $membriLC = FantaCISFMember::filter(role: 2);
        $membriOC = FantaCISFMember::filter(role: 3);
        $mymembers = FantaCISFTeam::filter(user: $user);
        $selected = array();
        foreach($mymembers as $member) {
            $selected[] = $member->teamMember->id;
        }

        return $this->render("FantaCISF/index", [
            "EC" => $membriEC,
            "LC" => $membriLC,
            "OC" => $membriOC,
            "selected" => $selected,
            "user" => $user
        ]);

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
        return $this->render("FantaCISF/teamNameForm", ["show_toast" => true], headers: ["HX-Trigger" => "showToast"]);
    }
}
