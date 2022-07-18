<?php

namespace BlackJackGame;

use Exception;

class Card
{
    private const SUITS_NAME = ['スペード','ハート','ダイヤ','クラブ',];

    private const NUMBER_NAME = [
        'A','2','3','4','5','6','7','8','9','10','J','Q','K',
    ];

    private const MAX_NUMBER = 13;
    private int $suitNumber;
    private int $number;

    public function __construct(int $suitNumber, int $number)
    {
        $isCorrectSuit = array_key_exists($suitNumber, $this::SUITS_NAME);
        $isCorrectNumber = $number >= 1 && $number <= $this::MAX_NUMBER;
        try {
            if (!$isCorrectSuit) {
                throw new Exception('指定した絵柄が正しくありません' . PHP_EOL);
            } elseif (!$isCorrectNumber) {
                throw new Exception('指定する数字は1~13の範囲で指定してください' . PHP_EOL);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $this->suitNumber = $suitNumber;
        $this->number = $number;
    }

    public function getSuitName(): string
    {
        return $this::SUITS_NAME[$this->suitNumber];
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getNumberName(): string
    {
        return $this::NUMBER_NAME[$this->number - 1];
    }
}
