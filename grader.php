<?php

namespace BlackJackGame;

class Grader
{
    private const POINTS = [
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        '10' => 10,
        '11' => 10,
        '12' => 10,
        '13' => 10,
    ];

    private const ACE_POINT = 11;
    private const MAX_SCORE = 21;
    private const INITIAL_SHEETS = 2;
    private const WINNER_MAGNIFICATION = 2.0;
    private const DRAW_MAGNIFICATION = 1.0;
    private const LOSER_MAGNIFICATION = 0.0;
    private const BLACK_JACK_MAGNIFICATION = 2.5;

    public function announceScore(Player $player, bool $isLast): void
    {
        $isNow = $isLast ? '' : 'の現在';
        echo $player->getPlayerName() . $isNow . 'の得点は' . $player->GetScore() . 'です。' . PHP_EOL;
        echo PHP_EOL;
    }

    public function scoringCard(Player $player): bool
    {
        $cards = $player->getDeck()->getCards();
        $point = 0;
        $isExistOne = false;
        foreach ($cards as $card) {
            $cardNumber = $card->getNumber();
            if ($cardNumber === 1 && !$isExistOne) {
                $isExistOne = true;
                continue;
            }
            $point += $this::POINTS[$cardNumber];
        }
        if ($isExistOne) {
            $point = $point + $this::ACE_POINT <= 21 ? $point + $this::ACE_POINT : $point + 1;
        }
        if ($point === $this::MAX_SCORE) {
            echo 'ブラックジャック！' . PHP_EOL;
        }
        if ($point > $this::MAX_SCORE) {
            echo $player->getPlayerName() .  'はバーストしました...' . PHP_EOL;
            $point = 0;
        }
        $player->setScore($point);
        return $point === 0 || $point === $this::MAX_SCORE;
    }

    /**
     * @param array<Player> $players
     * @return array<int,float>
     */
    public function judgeWinner(array $players): array
    {
        $betMagnifications = [];
        $scores = [];
        foreach ($players as $player) {
            $eachScore = $player->getIsSurrender() ? 0 : $player->getScore();
            echo $player->getPlayerName() . 'の得点は' . $eachScore . 'です' . PHP_EOL;
            if ($eachScore === 21 && $player->getDeck()->getSheetsNumber() === $this::INITIAL_SHEETS) {
                $eachScore++;
            }
            $scores[] = $eachScore;
        }
        echo PHP_EOL;

        $dealerScore = end($scores);
        foreach ($players as $order => $player) {
            if ($order === count($players) - 1) {
                continue;
            }

            $result = $this->resultSetting($scores[$order], $dealerScore);

            echo $player->getPlayerName() . $result['result'] . PHP_EOL;
            $betMagnifications[] = $result['betMagnification'];
        }
        echo PHP_EOL;
        return $betMagnifications;
    }

    /**
     * @return array<string|float>
     */
    private function resultSetting(int $score, int $dealerScore): array
    {
        $result = 'は引き分けました。';
        $betMagnification = $this::DRAW_MAGNIFICATION;
        if ($score === 0 || $score < $dealerScore) {
            $result = 'は負けました...';
            $betMagnification = $this::LOSER_MAGNIFICATION;
        }
        if ($score > $dealerScore) {
            $result = 'は勝ちました！';
            $isBlackJackScore = $score === $this::MAX_SCORE + 1;
            $betMagnification = $isBlackJackScore ? $this::BLACK_JACK_MAGNIFICATION : $this::WINNER_MAGNIFICATION;
        }
        return $result = [
            'result' => $result,
            'betMagnification' => $betMagnification,
        ];
    }
}
