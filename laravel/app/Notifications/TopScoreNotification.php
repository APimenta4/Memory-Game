<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TopScoreNotification extends Notification
{
    protected $game_id;
    protected $winner;
    protected $scope;
    protected $board_size;
    protected $position;
    protected $score_type;
    protected $score;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\User  $winner      The user who reached the top position.
     * @param  string           $scope        The scope ('personal' or 'global').
     * @param  string           $board_size   The size of the game board.
     * @param  int              $position     The new position of the user in the top 3.
     * @param  string           $score_type   The type of score (turns' or 'time').
     * @param  int              $score        The actual score that qualifies the user.
     */
    public function __construct($game_id, $winner, $scope, $board_size, $position, $score_type, $score)
    {
        $this->game_id = $game_id;
        $this->winner = $winner;
        $this->scope = $scope;
        $this->board_size = $board_size;
        $this->position = $position;
        $this->score_type = $score_type;
        $this->score = $score;
    }


    /**
     * Get the array representation of the notification for database storage.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'game_id' => $this->game_id,
            'winner_user_id' => $this->winner->id,
            'winner' => $this->winner->nickname,
            'scope' => $this->scope,
            'board_size' => $this->board_size,
            'position' => $this->position,
            'score_type' => $this->score_type,
            'score' => $this->score,
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }
}
