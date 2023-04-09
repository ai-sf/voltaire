<?php

namespace App\Controllers;

use Lepton\Base\{Application, Mailer};
use Lepton\Controller\BaseController;
use Lepton\Boson\Model;
use Liquid\{Liquid, Template};

use App\Models\{FantaCISFBonus, FantaCISFMember, FantaCISFPoints, User, Poll, PollAnswer, Vote, FantaCISFTeam};
use Lepton\Authenticator\LoginRequired;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Http\HttpResponse;

class AdminController extends BaseController
{
    public string $baseLink = "admin";
    protected array $default_parameters = [
        "nav" => [
            [
                "title" => "Home",
                "link" => "admin",
                "icon" => "house-door-fill",
                "min_level" => 2
            ],
            [
                "title" => "Votazioni",
                "link" => "admin/polls",
                "icon" => "bar-chart-fill",
                "min_level" => 3,
                "subnav" => [
                    [
                        "title" => "Tutte le votazioni",
                        "link" => "admin/polls"
                    ],
                    [
                        "title" => "Nuova votazione",
                        "link" => "admin/polls/new"
                    ]
                ]
            ],
            [
                "title" => "Utenti",
                "link" => "admin/users",
                "icon" => "people-fill",
                "min_level" => 3,
                "subnav" => [
                    [
                        "title" => "Tutti gli utenti",
                        "link" => "admin/users"
                    ],
                    [
                        "title" => "Nuovo utente",
                        "link" => "admin/users/new"
                    ]
                ]
            ],
            [
                "title" => "Proiettore",
                "link" => "admin/projector",
                "icon" => "projector-fill",
                "min_level" => 3,
                "target" => "_blank"
            ],
            [
                "title" => "FantaCISF",
                "link" => "admin/fantacisf/teams",
                "icon" => "trophy-fill",
                "min_level" => 2,
                "subnav" => [
                    [
                        "title" => "Squadre",
                        "link" => "admin/fantacisf/teams"
                    ],
                    [
                        "title" => "Assegna bonus",
                        "link" => "admin/fantacisf/bonuses"
                    ]
                ]
            ]

        ]
    ];

    #[LoginRequired(3)]
    public function index()
    {
        $polls = Poll::all();

        return $this->render("Admin/index", ["polls" => $polls]);
    }


    #[LoginRequired(3)]
    public function poll($id)
    {
        $poll = Poll::get($id);
        $user = User::get(name: "Roberto");
        $votes = Vote::filter(poll: $poll, user: $user)->count();
        if ($votes >= $user->votes) {
            return $this->render("Site/error", ["message" => "Hai già votato!"]);
        }
        $answers = PollAnswer::filter(poll: $poll);
        return $this->render("Site/poll", ["poll" => $poll, "answers" => $answers]);
    }


    #[LoginRequired(3)]
    public function newPoll()
    {
        $answers =  [
                ["id" => "n1", "title" => "Favorevole"],
                ["id" => "n2", "title" => "Contrario"]
        ];

        return $this->render("Admin/Polls/newPoll", ["answers" => $answers]);
    }

    #[LoginRequired(3)]
    public function editPoll($id)
    {
        $poll = Poll::get($id);
        $answers = PollAnswer::filter(poll: $poll);
        return $this->render("Admin/Polls/editPoll", ["answers" => $answers, "poll" => $poll]);
    }


    #[LoginRequired(3)]
    public function savePoll()
    {

        if (array_key_exists("poll-id", $_POST)) {
            $poll = Poll::get($_POST["poll-id"]);
            $poll->title = $_POST["question"];
            $poll->access_code = $_POST["access_code"];
            $poll->save();
            unset($_POST["poll-id"]);
        } else {
            $poll = Poll::new(title: $_POST["question"], description: "", access_code: $_POST["access_code"], active: 0);
            $poll->save();
        }
        unset($_POST["question"]);

        $answers = PollAnswer::filter(poll: $poll);
        foreach ($answers as $answer) {
            if (array_key_exists("answer-".$answer->id, $_POST)) {
                $answer->title = $_POST["answer-".$answer->id];
                $answer->poll = $poll;
                $answer->save();
                unset($_POST["answer-".$answer->id]);
            } else {
                $answer->delete();
            }
        }

        foreach ($_POST as $name => $title) {
            $matches = array();
            if (preg_match("/-(?<id>n?\d+$)/", $name, $matches)) {
                $id = $matches["id"];
                if ($id[0] == "n") {
                    $answer = PollAnswer::new(poll: $poll, title: $title);
                    $answer->save();
                }
            }
        }

        $updatedAnswers = PollAnswer::filter(poll: $poll);

        return $this->render(
            "Admin/Polls/pollForm",
            ["poll" => $poll, "answers" => $updatedAnswers, "is_update" => true],
            ['HX-Trigger' => 'showToast']
        );
    }


    #[LoginRequired(3)]
    public function pollsList()
    {
        $polls = Poll::all()->order_by("timestamp DESC");
        return $this->render("Admin/Polls/pollsList", ["polls" => $polls]);
    }



    #[LoginRequired(3)]
    public function activatePoll()
    {
        $poll = Poll::get(id: $_POST["id"]);
        $poll->active = array_key_exists("active", $_POST) ? 1 : 0;
        $poll->save();

        $text = $poll->active ? "attivata" : "disattivata";

        return $this->render(
            "Admin/toaster",
            ["message" => "Votazione $text correttamente!"],
            headers: ['HX-Trigger' => 'showToast']
        );
    }

    #[LoginRequired(3)]
    public function showResults()
    {
        $poll = Poll::get(id: $_POST["id"]);
        $poll->show_results = array_key_exists("show_results", $_POST) ? 1 : 0;
        $poll->save();

        $text = $poll->show_results ? "mostrati" : "nascosti";

        return $this->render(
            "Admin/toaster",
            ["message" => "Risultati $text correttamente!"],
            headers: ['HX-Trigger' => 'showToast']
        );
    }



    #[LoginRequired(3)]
    public function deletePoll()
    {
        $toDelete = Poll::get(id: $_POST["id"]);
        $toDelete->delete();
        $polls = Poll::all();
        return $this->render("Admin/Polls/pollsTable", ["polls" => $polls, "message" => "Votazione eliminata con successo!", "is_update"=>true], ['HX-Trigger' => 'showToast']);
    }


    #[LoginRequired(3)]
    public function pollResults($id)
    {
        $poll = Poll::get($id);

        return $this->render("Admin/Polls/pollResults", [
            "poll" => $poll,
        ]);
    }


    #[LoginRequired(3)]
    public function pollGraph($id)
    {
        $poll = Poll::get($id);
        $votes = Vote::filter(poll: $poll)->count();
        $answers = PollAnswer::filter(poll: $poll);
        $tot_users = User::filter(active: 1)->count();

        return $this->render("Admin/Polls/pollGraph", [
            "poll" => $poll,
            "votes" => $votes,
            "answers" => $answers,
            "tot_users" => $tot_users
        ]);
    }







    /* =================================== USERS ==================================== */

    #[LoginRequired(3)]
    public function usersList()
    {
        $users = User::all();
        return $this->render("Admin/Users/usersList", ["users" => $users, "num_users" => $users->count()]);
    }


    #[LoginRequired(3)]
    public function activateUser($id, $status=0)
    {
        $user = User::get(id: $id);
        $user->active = $status;
        $user->save();

        if ($user->active) {
            $text = "attivato";
        } else {
            $text = "disattivato";
        }
        return $this->render(
            "Admin/toaster",
            ["message" => "Utente $text correttamente!"],
            headers: ['HX-Trigger' => 'showToast']
        );
    }

    #[LoginRequired(3)]
    public function toggleOnline(int $id, $online = 0)
    {
        $user = User::get($id);
        $user->online = $online;
        $user->save();
        return $this->render(
            "Admin/toaster",
            ["message" => "Salvato correttamente!"],
            headers: ['HX-Trigger' => 'showToast']
        );
    }



    #[LoginRequired(3)]
    public function newUser()
    {
        return $this->render("Admin/Users/newUser");
    }


    #[LoginRequired(3)]
    public function saveUser()
    {

        $user = $this->doUserSave(
            id: isset($_POST["user-id"]) ? $_POST["user-id"] : null,
            email: $_POST["email"],
            level: $_POST["level"],
            name: $_POST["name"],
            surname: $_POST["surname"],
            votes: intval($_POST["votes"]),
            online: isset($_POST["online"]) ? $_POST["online"] : 0,
            active: isset($_POST["active"]) ? $_POST["active"] : 0,
        );

        $vars = [
            "is_update" => true,
            "message" => "Utente salvato correttamente"
        ];

        if(isset($_POST["user-id"])) {
            $vars["user"] = $user;
        }

        return $this->render(
            "Admin/Users/userForm",
            $vars,
            ['HX-Trigger' => 'showToast']
        );
    }

    #[LoginRequired(3)]
    private function doUserSave($id = null, $email, $level, $name, $surname, $votes, $online, $active = null)
    {
        $user = null;
        if (! is_null($id)) {
            $user = User::get($id);
            $user->email =  $email;
            $user->level = $level;
        } else {
            $authenticator = new UserAuthenticator();
            $user = $authenticator->register($email, $level);
        }

        if ($user) {
            $user->name = $name;
            $user->surname = $surname;
            $user->active = is_null($active) ? 0 : $active;
            $user->votes = $votes;
            $user->online = $online;
            $user->save();
            return $user;
        } else {
            return 0;
        }
    }


    #[LoginRequired(3)]
    public function userBatchUpload()
    {

        if (isset($_FILES['csvfile'])) {
            $n = 0;
            $csvfile = $_FILES['csvfile']['tmp_name'];
            if (($handle = fopen($csvfile, "r")) !== false) {
                fgetcsv($handle, 1000, ",");
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    if($this->doUserSave(
                        id: null,
                        email: $data[2],
                        level: intval($data[3]),
                        name: $data[0],
                        surname: $data[1],
                        online: $data[5],
                        votes: intval($data[4])
                    )) {
                        $n++;
                    }

                }
                fclose($handle);
            }
        }

        return new HttpResponse(
            200,
            headers: ["HX-Trigger" => "reload-users"],
            body: "<div id='post-result-inner' class='rounded container bg-success text-white py-2 px-3 small'>Caricati $n utenti</div>"
        );
    }


    #[LoginRequired(3)]
    public function sendMail(int $id)
    {
        $authenticator = new UserAuthenticator();
        $password = $authenticator->passwordReset($id);
        $user = User::get($id);
        $config = Application::getEmailConfig();
        $mail = new Mailer();

        $subject = 'Credenziali sistema di voto AGA 2023';
        $body = $this->render("Admin/loginEmail", ["name" => $user->name, "username" => $user->email, "password" => $password]);//sprintf("Username: %s <br/>Password: %s", $user->email, $password);

        if ($mail->send($user->email, $subject, $body)) {
            $message = "Email inviata correttamente";
        } else {
            $message = "Errore di invio";
        }
        return $this->render(
            "Admin/toaster",
            ["message" => $message],
            headers: ['HX-Trigger' => 'showToast']
        );
    }



    #[LoginRequired(3)]
    public function deleteUser($id)
    {
        $toDelete = User::get(id: $id);
        $toDelete->delete();
        $users= User::all();
        return $this->render(
            "Admin/Users/usersTable",
            [
                "users" => $users,
                "is_update"=>true,
                "message" => "Utente rimosso correttamente"
            ],
            ['HX-Trigger' => 'showToast']
        );
    }


    #[LoginRequired(3)]
    public function editUser($id)
    {
        $user = User::get($id);
        return $this->render("Admin/Users/editUser", ["user" => $user]);
    }


    #[LoginRequired(3)]
    public function userSearch()
    {
        $allowed = ["name", "surname", "level", "active", "online"];
        $filters = array();

        foreach($allowed as $filter) {
            if(isset($_POST[$filter]) && $_POST[$filter] != "") {
                $filters[$filter."__startswith"] = $_POST[$filter];
            }
        }
        if(count($filters)> 0) {
            $users = User::filter(...$filters);
        } else {
            $users = User::all(...$filters);
        }
        return $this->render("Admin/Users/usersTable", ["is_update"=>true, "users" => $users, "num_users" => $users->count()]);
    }

    #[LoginRequired(3)]
    public function batchAction()
    {
        foreach($_POST["user-checkbox"] as $id) {
            switch ($_POST["action"]) {
                case 'activate':
                    $this->activateUser($id, 1);
                    break;
                case 'deactivate':
                    $this->activateUser($id, 0);
                    break;
                case 'delete':
                    $this->deleteUser($id);
                    break;
                case 'sendmail':
                    sleep(1);
                    $this->sendMail($id);
                    break;
                default:
                    break;
            }
        }
        return $this->render(
            "Admin/toaster",
            ["message" => "Azione eseguita con successo"],
            headers: ["HX-Trigger" => '{"showToast" : "", "reload-users": ""}']
        );
    }


    #[LoginRequired(2)]
    public function fantaCisfTeams(){
        $users = User::filter(fantacisf_team__neq: "");
        $standings = array();

        foreach($users as $user){
            $standings[] = [
                "id" => $user->id,
                "name" => $user->name." ".$user->surname,
                "team_name" => $user->fantacisf_team,
                "points" => (new FantaCISFController)->computePointsUser($user),
                "team" => FantaCISFTeam::filter(user: $user)->do()
            ];
        }

        usort($standings, array(FantaCISFController::class, "cmp"));
        return $this->render("Admin/FantaCISF/league", ["users" => $standings,
        "num_teams" => $users->count()]);
    }

    #[LoginRequired(2)]
    public function fantacisfBonuses(){
        $members = FantaCISFMember::all()->order_by("name");
        return $this->render("Admin/FantaCISF/bonuses", ["members" => $members]);
    }

    #[LoginRequired(2)]
    public function fantacisfBonusesMember($id){
        $member = FantaCISFMember::get($id);
        $bonuses = FantaCISFBonus::all()->order_by("id");
        $memberBonuses = array();

        foreach($bonuses as $bonus){
            $counts = FantaCISFPoints::filter(member: $member, bonus: $bonus)->count();
            $memberBonuses[] = [
                "id" => $bonus->id,
                "name" => $bonus->name,
                "points" => $bonus->points,
                "times" => $counts
            ];
        }

        return $this->render("Admin/FantaCISF/memberBonuses", ["member" => $member, "bonuses" => $memberBonuses]);
    }


    #[LoginRequired(2)]
    public function setBonus($member_id, $bonus_id){
        $member = FantaCISFMember::get($member_id);
        $bonus = FantaCISFBonus::get($bonus_id);
        $points = FantaCISFPoints::new(member: $member, bonus: $bonus);
        $points->save();

        return $this->render(
            "Admin/toaster",
            ["message" => "Bonus assegnato correttamente"],
            headers: ['HX-Trigger' => 'showToast']
        );
    }



    #[LoginRequired(2)]
    public function removeBonus($member_id, $bonus_id){
        $member = FantaCISFMember::get($member_id);
        $bonus = FantaCISFBonus::get($bonus_id);
        $points = FantaCISFPoints::filter(member: $member, bonus: $bonus);
        if($points->count() > 0){
            $points->first()->delete();
            return $this->render(
                "Admin/toaster",
                ["message" => "Bonus rimosso correttamente"],
                headers: ['HX-Trigger' => 'showToast']
            );
        }
        return new HttpResponse(200, body: "");

    }

}
