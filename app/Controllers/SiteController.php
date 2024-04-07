<?php

namespace App\Controllers;

use Lepton\Core\Application;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Controller\BaseController;
use Liquid\{Liquid, Template};

use App\Models\{User, Poll, PollAnswer, Vote};
use Lepton\Authenticator\AccessControlAttributes\LoginRequired;
use Lepton\Exceptions\MultipleFieldAttributeException;
use Lepton\Http\Response\HttpResponse;

class SiteController extends BaseController
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




    public function login()
    {
        if (isset($_POST["email"]) && isset($_POST["password"])) {
            $authenticator = new UserAuthenticator();
            if (!$authenticator->login($_POST["email"], $_POST["password"])) {
                return $this->render(
                    "Site/loginForm",
                    [
                      "login_invalid" => true,
                      "login_message" => "Username e/o password errati"
                    ]
                );
            } else {
                if (isset($_SESSION["redirect_url"])) {
                    $response = $this->redirect($_SESSION["redirect_url"], htmx:true, parse:false);
                    unset($_SESSION['redirect_url']);
                    return $response;
                }
                return $this->redirect("", htmx:true);
            }
        }
        return $this->render("Site/login");
    }


    #[LoginRequired(level: 1)]
    public function logout()
    {
        $authenticator = new UserAuthenticator();
        $authenticator->logout();
        return $this->redirect("login");
    }

    #[LoginRequired(level: 1)]
    public function index()
    {
        $toVote = array();
        $voted = array();
        $authenticator = new UserAuthenticator();
        $user = $authenticator->getLoggedUser();

        if($user->active) {
            $polls = Poll::all();

            foreach ($polls as $poll) {
                $votes = Vote::filter(poll: $poll, user: $user)->count();
                if (($votes < $user->votes) && ($poll->active)) {
                    $toVote[] = [
                        "id" => $poll->id
                        ];
                } else {
                    if($votes >= $user->votes) {
                        $voted[] = ["id" => $poll->id];
                    }
                }
            }
            return $this->render("Site/index", ["polls" => $toVote, "completed_polls" => $voted]);
        } else {
            return $this->render("Site/error", ["message" => "Non sei abilitato/a al voto"]);
        }

    }


    #[LoginRequired(level: 1)]
    public function poll($id)
    {
        $poll = Poll::get($id);
        $user = (new UserAuthenticator())->getLoggedUser();
        $votes = Vote::filter(poll: $poll, user: $user)->count();
        if ($votes >= $user->votes) {
            return $this->render("Site/completedPoll", ["votes" => 0, "poll" => $poll, "message" => "Hai già votato!"]);
        }


        if((! $poll->active)) {
            if($votes <= $user->votes) {
                $ac = new AdminController();
                $infos = $ac->getPollInfo($poll);
                return $this->render("Site/pollGraph", [
                    "poll" => $poll, "infos" => $infos,
                ]);
            } else {
                return $this->render("Site/error", ["message" => "Votazione chiusa"]);

            }
        }



        if(isset($_POST["access_code"])) {

            unset($_POST["access_code"]);
            $access_code = implode($_POST);
            if($access_code === $poll->access_code) {

                $answers = PollAnswer::filter(poll: $poll);
                return $this->render(
                    "Site/poll",
                    [
                        "poll" => $poll,
                        "answers" => $answers,
                        "votes" => $user->votes - $votes,
                    ]
                );
            } else {
                return $this->render("Site/accessCodePoll", ["poll" => $poll, "error_message" => "Codice errato", "votes" => $user->votes - $votes]);
            }
        } else {
            return $this->render("Site/accessCodePoll", ["poll" => $poll, "votes" => $user->votes - $votes]);
        }
    }


    #[LoginRequired(level: 1)]
    public function pollVote()
    {
        $authenticator = new UserAuthenticator();
        $user = $authenticator->getLoggedUser();

        $answers = PollAnswer::filter(poll_id: $_POST["poll-id"], id: $_POST["answer"]);
        $answer_count = $answers->count();
        $poll = Poll::get($_POST["poll-id"]);
        $votes = Vote::filter(poll: $poll, user: $user)->count();

        if($user->active) {
            if($answer_count == 1) {

                $answer = $answers->first();

                if ($votes < $user->votes) {
                    if($poll->active == 1) {
                        $vote = Vote::new(poll: $poll, user: $user);
                        $answer->votes = $answer->votes + 1;
                        $answer->save();
                        $vote->save();
                        $votes = Vote::filter(poll: $poll, user: $user)->count();

                        if($votes + 1 < $user->votes) {
                            $answers = PollAnswer::filter(poll: $poll);
                            return $this->render("Site/poll", ["poll" => $poll, "votes" => $user->votes - $votes, "answers" => $answers]);
                        } else {

                            return $this->render("Site/completedPoll", ["message" => "Grazie per aver votato", "poll" => $poll]);
                        }
                    } else {
                        $message = "Sondaggio chiuso";
                    }

                } else {
                    $message = "Hai già votato il numero massimo di volte!";
                }
            } else {
                $message = "Risposta inesistente.";
            }
        } else {
            $message = "Non sei abilitato/a al voto.";
        }

        return new HttpResponse(200, body: $message);

    }
}
