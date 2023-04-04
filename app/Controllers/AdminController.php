<?php

namespace App\Controllers;

use Lepton\Base\{Application, Mailer};
use Lepton\Controller\BaseController;
use Lepton\Boson\Model;
use Liquid\{Liquid, Template};

use App\Models\{User, Poll, PollAnswer, Vote};
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
                "icon" => "house-door-fill"
            ],
            [
                "title" => "Votazioni",
                "link" => "admin/polls",
                "icon" => "bar-chart-fill",
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
                "target" => "_blank"
            ]

        ]
    ];

    #[LoginRequired(2)]
    public function index()
    {
        $polls = Poll::all();

        return $this->render("Admin/index", ["polls" => $polls]);
    }


    #[LoginRequired(2)]
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


    #[LoginRequired(2)]
    public function newPoll()
    {
        $answers =  [
                ["id" => "n1", "title" => "Favorevole"],
                ["id" => "n2", "title" => "Contrario"]
        ];

        return $this->render("Admin/Polls/newPoll", ["answers" => $answers]);
    }

    #[LoginRequired(2)]
    public function editPoll($id)
    {
        $poll = Poll::get($id);
        $answers = PollAnswer::filter(poll: $poll);
        return $this->render("Admin/Polls/editPoll", ["answers" => $answers, "poll" => $poll]);
    }


    #[LoginRequired(2)]
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


    #[LoginRequired(2)]
    public function pollsList()
    {
        $polls = Poll::all()->order_by("timestamp DESC");
        return $this->render("Admin/Polls/pollsList", ["polls" => $polls]);
    }



    #[LoginRequired(2)]
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

    #[LoginRequired(2)]
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



    #[LoginRequired(2)]
    public function deletePoll()
    {
        $toDelete = Poll::get(id: $_POST["id"]);
        $toDelete->delete();
        $polls = Poll::all();
        return $this->render("Admin/Polls/pollsTable", ["polls" => $polls, "message" => "Votazione eliminata con successo!", "is_update"=>true], ['HX-Trigger' => 'showToast']);
    }


    #[LoginRequired(2)]
    public function pollResults($id)
    {
        $poll = Poll::get($id);

        return $this->render("Admin/Polls/pollResults", [
            "poll" => $poll,
        ]);
    }


    #[LoginRequired(2)]
    public function pollGraph($id)
    {
        $poll = Poll::get($id);
        $votes = Vote::filter(poll: $poll)->count();
        $answers = PollAnswer::filter(poll: $poll);

        return $this->render("Admin/Polls/pollGraph", [
            "poll" => $poll,
            "votes" => $votes,
            "answers" => $answers
        ]);
    }







    /* =================================== USERS ==================================== */

    #[LoginRequired(2)]
    public function usersList()
    {
        $users = User::all();
        return $this->render("Admin/Users/usersList", ["users" => $users, "num_users" => $users->count()]);
    }


    #[LoginRequired(2)]
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


    #[LoginRequired(2)]
    public function newUser()
    {
        return $this->render("Admin/Users/newUser");
    }


    #[LoginRequired(2)]
    public function saveUser()
    {

        $user = $this->doUserSave(
            isset($_POST["user-id"]) ? $_POST["user-id"] : null,
            $_POST["email"],
            $_POST["level"],
            $_POST["name"],
            $_POST["surname"],
            isset($_POST["active"]) ? $_POST["active"] : 0,
            $_POST["votes"]
        );


        return $this->render(
            "Admin/Users/userForm",
            [
                "user" => $user,
                "is_update" => true,
                "message" => "Utente salvato correttamente"
            ],
            ['HX-Trigger' => 'showToast']
        );
    }


    private function doUserSave($id = null, $email, $level, $name, $surname, $votes, $active = null)
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
            $user->save();
            return $user;
        } else {
            return 0;
        }
    }


    #[LoginRequired(2)]
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


    #[LoginRequired(2)]
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



    #[LoginRequired(2)]
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


    #[LoginRequired(2)]
    public function editUser($id)
    {
        $user = User::get($id);
        return $this->render("Admin/Users/editUser", ["user" => $user]);
    }


    #[LoginRequired(2)]
    public function userSearch()
    {
        $allowed = ["name", "surname", "level", "active"];
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
        return $this->render("Admin/Users/usersTable", ["users" => $users, "num_users" => $users->count()]);
    }

    #[LoginRequired(2)]
    public function batchAction()
    {

        $actionsMap = [
            "activate" => "activateUser",
            "deactivate" => "activateUser",
            "delete" => "deleteUser"
        ];

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
/*
    public function loginEmailPreview(){
        return $this->render("Admin/loginEmail", ["name" => "Roberto", "username" => "rrr@gmail.com", "password" => "culo"]);
    }*/
}
