<?php

namespace BlackJackGame;

interface ActionHand
{
    public function actionCardToPlayer(Player $target, Card $selectedCard, bool $isFlipUp): bool;

    public function changeBetAmount(BetManager $bitManager, int $order): void;

    public function getClassName(): string;

    public function setActionParameter(Player $player): void;
}
