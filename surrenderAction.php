<?php

namespace BlackJackGame;

use Exception;

class SurrenderAction implements ActionHand
{
    public function actionCardToPlayer(Player $target, Card $selectedCard, bool $isFlipUp): bool
    {
        return false;
    }

    public function changeBetAmount(BetManager $betManager, int $order): void
    {
        $nowBettedCoin = $betManager->getBettedCoinAmount($order);

        $newBettedCoin = (int)floor($nowBettedCoin / 2);
        $betManager->returnCoin($order, $newBettedCoin);
    }

    public function getClassName(): string
    {
        $texts = [explode('\\', __CLASS__)];
        return end($texts[0]);
    }

    public function setActionParameter(Player $player): void
    {
        $player->setIsSurrender();
    }
}
