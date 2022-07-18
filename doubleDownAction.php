<?php

namespace BlackJackGame;

class DoubleDownAction implements ActionHand
{
    public function actionCardToPlayer(Player $target, Card $selectedCard, bool $isFlipUp): bool
    {
        $target->getDeck()->addCard($selectedCard);

        $flipUpText = $target->getPlayerName() . 'の引いたカードは';
        $flipUpText = $flipUpText . $selectedCard->getSuitName() . 'の' . $selectedCard->getNumberName();
        $flipUpText = $flipUpText . 'です。' . PHP_EOL;

        $unFlipUpText = $target->getPlayerName() . 'の引いた' . $target->getDeck()->getSheetsNumber();
        $unFlipUpText = $unFlipUpText . '枚目のカードはわかりません' . PHP_EOL;

        echo $isFlipUp ? $flipUpText : $unFlipUpText;

        return true;
    }

    public function changeBetAmount(BetManager $betManager, int $order): void
    {
        $nowBettedCoin = $betManager->getBettedCoinAmount($order);
        $betManager->betCoin($order, $nowBettedCoin);
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
