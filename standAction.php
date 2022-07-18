<?php

namespace BlackJackGame;

class StandAction implements ActionHand
{
    public function actionCardToPlayer(Player $target, Card $selectedCard, bool $isFlipUp): bool
    {
        return false;
    }

    public function changeBetAmount(BetManager $betManager, int $order): void
    {
    }

    public function getClassName(): string
    {
        $texts = [explode('\\', __CLASS__)];
        return end($texts[0]);
    }

    public function setActionParameter(Player $player): void
    {
    }
}
