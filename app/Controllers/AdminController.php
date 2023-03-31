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
                        "title" => "Nuovo utente",
                        "link" => "admin/users/new"
                    ]
                ]
            ],

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
        $question = $_POST["question"];
        unset($_POST["question"]);
        if (array_key_exists("poll-id", $_POST)) {
            $poll = Poll::get($_POST["poll-id"]);
            $poll->title = $question;
            $poll->save();
            unset($_POST["poll-id"]);
        } else {
            $poll = Poll::new(title: $question, description: "", access_code: "0000", type: 1);
            $poll->save();
        }

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
        $polls = Poll::all();
        return $this->render("Admin/Polls/pollsList", ["polls" => $polls]);
    }



    #[LoginRequired(2)]
    public function activatePoll()
    {
        $poll = Poll::get(id: $_POST["id"]);
        $poll->type = array_key_exists("status", $_POST) ? 1 : 0;
        $poll->save();

        if ($poll->type) {
            $text = "attivato";
        } else {
            $text = "disattivato";
        }
        return new HttpResponse(200, body: "Poll $text correttamente!");
    }


    #[LoginRequired(2)]
    public function deletePoll()
    {
        $toDelete = Poll::get(id: $_POST["id"]);
        $toDelete->delete();
        $polls = Poll::all();
        return $this->render("Admin/Polls/pollsTable", ["polls" => $polls], ['HX-Trigger' => 'showToast']);
    }




    /* =================================== USERS ==================================== */

    #[LoginRequired(2)]
    public function usersList()
    {
        $users = User::all();
        return $this->render("Admin/Users/usersList", ["users" => $users]);
    }


    #[LoginRequired(2)]
    public function activateUser()
    {
        $user = User::get(id: $_POST["id"]);
        $user->active = array_key_exists("status", $_POST) ? 1 : 0;
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
        $authenticator = new UserAuthenticator();
        $user = $authenticator->register($_POST["email"], level: $_POST["level"]);
        $user->name = $_POST["name"];
        $user->surname = $_POST["surname"];
        $user->active = isset($_POST["active"]) ? $_POST["active"] : 0;
        $user->save();
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


    #[LoginRequired(2)]
    public function sendMail(int $id)
    {
        $authenticator = new UserAuthenticator();
        $password = $authenticator->passwordReset($id);
        $user = User::get($id);
        $config = Application::getEmailConfig();
        $mail = new Mailer();

        $subject = 'Credenziali sistema di voto AGA 2023';
        $body = sprintf("Username: %s <br/>Password: %s", $user->email, $password);

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
    public function deleteUser()
    {
        $toDelete = User::get(id: $_POST["id"]);
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
}
