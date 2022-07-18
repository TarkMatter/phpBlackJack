<?php

namespace BlackJackGame;

class Deck
{
    /**
     * @var array<Card> $cards
     */
    private array $cards;

    public function __construct()
    {
        $this->cards = [];
    }

    public function createPile(): string
    {
        foreach (range(0, 3) as $suit) {
            foreach (range(1, 13) as $number) {
                $this->cards[] = new Card($suit, $number);
            }
        }
        return '';
    }

    public function shuffleDeck(): string
    {
        shuffle($this->cards);
        return '';
    }

    public function addCard(Card $addCard): Card
    {
        $this->cards[] = $addCard;
        return $this->cards[count($this->cards) - 1];
    }

    public function getSheetsNumber(): int
    {
        return count($this->cards);
    }

    public function removeCard(Card $removeCard): bool
    {
        $targetIndex = null;
        foreach ($this -> cards as $key => $value) {
            if ($value == $removeCard) {
                $targetIndex = $key;
            }
        }
        if ($targetIndex !== null) {
            array_splice($this -> cards, $targetIndex, 1);
        }
        return in_array($removeCard, $this->cards);
    }

    public function randomDraw(): Card
    {
        $randomIndex = rand(0, count($this->cards) - 1);
        $selectedCard = $this->cards[$randomIndex];
        $this->removeCard($selectedCard);
        return $selectedCard;
    }

    /**
     * @return array<Card>
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    public function getCardSuit(int $number): string
    {
        return $this->cards[$number]->getSuitName();
    }

    public function getCardNumberName(int $number): string
    {
        return $this->cards[$number]->getNumberName();
    }

    /**
     * @return array<Card>
     */
    public function resetDeck(): array
    {
        $this->cards = [];
        return $this->cards;
    }
}
