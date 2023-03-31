<?php

namespace App\Controllers;

use Lepton\Base\Application;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Controller\BaseController;
use Liquid\{Liquid, Template};

use App\Models\{User, Poll, PollAnswer, Vote};
use Lepton\Authenticator\LoginRequired;
use Lepton\Exceptions\MultipleFieldAttributeException;
use Lepton\Http\HttpResponse;

class SiteController extends BaseController
{
    public string $baseLink = "";
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
        $polls = Poll::filter(type: 1);
        $toVote = array();
        $authenticator = new UserAuthenticator();
        $user = $authenticator->getLoggedUser();

        foreach ($polls as $poll) {
            $votes = Vote::filter(poll: $poll, user: $user)->count();
            if ($votes < $user->votes) {
                $toVote[] = [
                    "id" => $poll->id
                    ];
            }
        }
        return $this->render("Site/index", ["polls" => $toVote]);
    }


    #[LoginRequired(level: 1)]
    public function poll($id)
    {
        $poll = Poll::get($id);
        $user = User::get(name: "Roberto");
        $votes = Vote::filter(poll: $poll, user: $user)->count();
        if ($votes >= $user->votes) {
            return $this->render("Site/error", ["message" => "Hai già votato!"]);
        }
        $answers = PollAnswer::filter(poll: $poll);
        return $this->render("Site/poll",
            [
                "poll" => $poll,
                "answers" => $answers,
                "votes" => $user->votes-$votes,
            ]);
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

        if($user->active){
            if($answer_count == 1) {

                $answer = $answers->first();

                if ($votes < $user->votes) {
                    if($poll->type == 1){
                        $vote = Vote::new(poll: $poll, user: $user);
                        $answer->votes = $answer->votes + 1;
                        $vote->save();
                        if($votes + 1 < $user->votes){
                            $answers = PollAnswer::filter(poll: $poll);
                            return $this->render("Site/poll", ["poll" => $poll, "answers" => $answers]);
                        } else {
                            $message = "Grazie per aver votato";
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
