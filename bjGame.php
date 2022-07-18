<?php

namespace BlackJackGame;

require_once('deck.php');
require_once('card.php');
require_once('player.php');
require_once('betManager.php');
require_once('grader.php');
require_once('actionHand.php');
require_once('hitAction.php');
require_once('standAction.php');
require_once('doubleDownAction.php');
require_once('surrenderAction.php');

use BlackJackGame\HitAction;
use BlackJackGame\StandAction;
use BlackJackGame\DoubleDownAction;
use BlackJackGame\SurrenderAction;

$bjGame = new BJGame();
$bjGame->playBlackJackGame();

class BJGame
{
    /**
     * @var array<Player> $players
     */
    private array $players;
    private BetManager $betManager;
    private Deck $pile;
    private Grader $grader;
    private ActionHand $actionHand;

    private const CONTINUE_BORDER = 17;
    private const DEALER_2ND_HAND_INDEX = 1;
    private const REGULAR_SELECT_MESSAGES = [
        'ヒット / カードを引く ( HI )',
        'スタンド / もうカードを引かない ( ST )',
        'どのアクションを実行しますか？ : ',
    ];
    private const OPTION_SELECT_MESSAGES = [
        'ダブルダウン / ベットを倍にしてカードを１枚だけ引く ( DD )',
        'サレンダー / ベットの半額を渡して降参 ( SR )',
    ];

    private const HIT = 'HIT';
    private const STAND = 'STAND';
    private const DOUBLE_DOWN = 'DOUBLE_DOWN';
    private const SURRENDER = 'SURRENDER';

    private const REGULAR_ACTION_TYPE = [
        'HI' => 'HIT',
        'ST' => 'STAND',
    ];
    private const OPTION_ACTION_TYPE = [
        'DD' => 'DOUBLE_DOWN',
        'SR' => 'SURRENDER',
    ];

    public function gameSetting(): string
    {
        $this->players[] = new Player(new Deck(), false, 'あなた');
        $this->players[] = new Player(new Deck(), true, '対戦相手１');
        $this->players[] = new Player(new Deck(), true, 'ディーラー');

        $this->betManager = new BetManager();
        $this->grader = new Grader();
        $this->actionHand = new HitAction();

        foreach ($this->players as $order => $player) {
            if ($order === count($this->players)) {
                continue;
            }
            $this->betManager->setCarryCoins($order, $player->getCoinAmount());
        }

        return '';
    }

    public function playBlackJackGame(): string
    {
        echo 'ブラックジャックを開始します' . PHP_EOL;
        $judge = true;

        $this->gameSetting();

        while ($judge) {
            $this->createGamePile();

            $this->playerBetTurn();

            $this->firstDrawTurn();

            $this->turnDrawCycle();

            $betMagnification = $this->grader->judgeWinner($this->players);

            $this->betManager->calculationReward($betMagnification);

            foreach ($this->players as $player) {
                $player->resetParameter();
            }

            $judge = $this->confirmRepeatGame($this->players);
        }

        echo 'ブラックジャックを終了します' . PHP_EOL;

        return '';
    }

    private function createGamePile(): void
    {
        $this->pile = new Deck();
        $this->pile->createPile();
        $this->pile->shuffleDeck();
    }

    private function playerBetTurn(): void
    {
        foreach ($this->players as $order => $player) {
            if (!$player->isNpcPlayer()) {
                $this->betManager->firstBetInput($player, $order);
            }
        }
    }

    private function firstDrawTurn(): void
    {
        foreach ($this->players as $order => $player) {
            $isFlipUp = $order !== count($this->players) - 1;
            $selectedCard = $this->randomDrawFromPile();
            $this->actionHand->actionCardToPlayer($player, $selectedCard, true);
            $selectedCard = $this->randomDrawFromPile();
            $this->actionHand->actionCardToPlayer($player, $selectedCard, $isFlipUp);
            echo PHP_EOL;
        }
    }

    private function turnDrawCycle(): void
    {
        foreach ($this->players as $order => $player) {
            $this->actionHand = new HitAction();
            $isStandAction = false;
            $isSurrenderAction = false;
            $isDoubleDownAction = false;

            $actionSelectMessages = array_merge($this::OPTION_SELECT_MESSAGES, $this::REGULAR_SELECT_MESSAGES);

            $turnCount = 0;
            if ($this->isDealerTurn($order)) {
                echo $player->GetCardInfo($this::DEALER_2ND_HAND_INDEX) . PHP_EOL;
            }

            while (true) {
                $isLast = $this->scoringAnnounce($player, $isDoubleDownAction);
                if ($isLast) {
                    break;
                }

                $answer = $this->judgeNpcContinue($player);
                if (!$answer) {
                    break;
                }

                if (!$player->isNpcPlayer()) {
                    $this->actionHand = $this->questionActionSelect($actionSelectMessages, $order, $turnCount);
                    $this->actionHand->changeBetAmount($this->betManager, $order);
                    $this->actionHand->setActionParameter($player);
                    $actionSelectMessages = $this::REGULAR_SELECT_MESSAGES;
                }

                $isStandAction = ($this->actionHand->getClassName() == 'StandAction');
                $isSurrenderAction = ($this->actionHand->getClassName() == 'SurrenderAction');
                $isDoubleDownAction = ($this->actionHand->getClassName() == 'DoubleDownAction');
                if ($isStandAction || $isSurrenderAction) {
                    break;
                }
                $selectedCard = $this->randomDrawFromPile();
                $this->actionHand->actionCardToPlayer($player, $selectedCard, true);
                $turnCount++;
            }
        }
    }

    private function scoringAnnounce(Player $player, bool $isDoubleDownAction): bool
    {
        $isGameOver = $this->grader->scoringCard($player);
        $isLast = $isGameOver || $isDoubleDownAction;
        $this->grader->announceScore($player, $isLast);
        return $isLast;
    }

    private function isDealerTurn(int $order): bool
    {
        return $order === count($this->players) - 1;
    }

    private function randomDrawFromPile(): Card
    {
        $selectedCard = $this->pile->randomDraw();
        $this->pile->shuffleDeck();
        return $selectedCard;
    }

    /**
     * @param array<string> $selectMessages
     */
    private function questionActionSelect(array $selectMessages, int $order, int $turnCount): ActionHand
    {
        $isSelectCompleted = false;
        $actionHand = new HitAction();

        $questionText = implode("\n", $selectMessages);
        $inputCheckArray = $this::REGULAR_ACTION_TYPE;
        if ($turnCount === 0) {
            $inputCheckArray = array_merge($inputCheckArray, $this::OPTION_ACTION_TYPE);
        }
        do {
            echo $questionText;
            $input = Trim(fgets(STDIN));
            $input = strtoupper($input);
            $isExist = array_key_exists($input, $inputCheckArray);
            echo PHP_EOL;
            if (!$isExist) {
                echo '正しい手を選択してください' . PHP_EOL;
                continue;
            }
            $selectedActionType = $inputCheckArray[$input];
            switch ($selectedActionType) {
                case $this::HIT:
                    $isSelectCompleted = true;
                    break;
                case $this::STAND:
                    $actionHand = new StandAction();
                    $isSelectCompleted = true;
                    break;
                case $this::DOUBLE_DOWN:
                    $betted = $this->betManager->getBettedCoinAmount($order);
                    $rest = $this->betManager->getRestCoinAmount($order);
                    if ($rest < $betted) {
                        $isSelectCompleted = false;
                        echo 'ダブルダウンするコインが足りません' . PHP_EOL;
                        break;
                    }
                    $actionHand = new DoubleDownAction();
                    $isSelectCompleted = true;
                    break;
                case $this::SURRENDER:
                    $actionHand = new SurrenderAction();
                    $isSelectCompleted = true;
                    break;
            }
            echo PHP_EOL;
        } while (!$isSelectCompleted);
        return $actionHand;
    }

    private function questionYesNo(string $questionText): bool
    {
        $answer = false;
        $input = '';
        do {
            echo $questionText;
            $input = Trim(fgets(STDIN));
            $input = strtoupper($input);
            switch ($input) {
                case 'Y':
                    $answer = true;
                    break;
                case 'N':
                    $answer = false;
                    break;
                default:
                    echo 'Y/Nで回答してください' . PHP_EOL;
                    break;
            }
        } while ($input !== 'Y' && $input !== 'N');
        return $answer;
    }

    private function judgeNpcContinue(Player $player): bool
    {
        if (!$player->isNpcPlayer()) {
            return true;
        }
        $score = $player->getScore();
        return $score !== 0 && $score < $this::CONTINUE_BORDER;
    }

    /**
     * @param array<Player> $players
     */
    private function confirmRepeatGame(array $players): bool
    {
        foreach ($players as $order => $player) {
            if ($player->isNpcPlayer()) {
                continue;
            }
            $coin = $this->betManager->getRestCoinAmount($order);
            if ($coin <= 0) {
                echo '最低一人のコインがなくなりました。' . PHP_EOL;
                return false;
            }
        }

        foreach ($players as $order => $player) {
            if ($player->isNpcPlayer()) {
                continue;
            }
            $confirmText = $player->getPlayerName() . 'はゲームを続行しますか？' . PHP_EOL;
            $confirmText = $confirmText . '(一人でも同意しなければゲーム終了です) : ';
            $answer = $this->questionYesNo($confirmText);
            if (!$answer) {
                return false;
            }
        }
        return true;
    }
}
