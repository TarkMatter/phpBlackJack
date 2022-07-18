<?php

namespace BlackJackGame;

class Player
{
    private static int $allPlayerNumber = 0;
    private string $name;
    private Deck $deck;
    private int $score;
    private int $coin;
    private bool $isNPC;
    private bool $isSurrender;

    public function __construct(Deck $newDeck, bool $isNPC, string $name = null)
    {
        $this->name = 'プレイヤー' . strval(self::$allPlayerNumber);
        if ($name !== null) {
            $this->name = $name;
        }
        self::$allPlayerNumber++;

        $this->deck = $newDeck;
        $this->score = 0;
        $this->coin = 100;
        $this->isNPC = $isNPC;
        $this->isSurrender = false;
    }

    public function getPlayerName(): string
    {
        return $this->name;
    }

    public function getDeck(): Deck
    {
        return $this->deck;
    }

    public function getCardInfo(int $number): string
    {
        $text = $this->name . 'の引いた' . ($number + 1) . '枚目のカード';
        $text = $text . 'は' . $this->deck->getCardSuit($number) . 'の';
        $text = $text . $this->deck->getCardNumberName($number);
        $text = $text . 'です。';
        return $text;
    }

    public function setScore(int $score): int
    {
        $this->score = $score;
        return $this->score;
    }

    public function getScore(): int
    {
        return $this->isSurrender ? 0 : $this->score;
    }

    public function getDeckCardSuit(int $number): string
    {
        return $this->deck->getCardSuit($number);
    }

    public function isNpcPlayer(): bool
    {
        return $this->isNPC;
    }

    public function getCoinAmount(): int
    {
        return $this->coin;
    }

    public function addCoin(int $coinAmount): void
    {
        $this->coin += $coinAmount;
    }

    public function setIsSurrender(): void
    {
        $this->isSurrender = true;
    }

    public function getIsSurrender(): bool
    {
        return $this->isSurrender;
    }

    public function resetParameter(): void
    {
        $this->getDeck()->resetDeck();
        $this->score = 0;
        $this->isSurrender = false;
    }
}
