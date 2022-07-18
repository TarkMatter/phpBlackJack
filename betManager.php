<?php

namespace BlackJackGame;

use Exception;

class BetManager
{
    /**
     * @var array<array<int,int>> $bettedCoins
     */
    private array $bettedCoins;
    private const BETTED_CELL = 0;
    private const REST_CELL = 1;

    public function __construct()
    {
        $this->bettedCoins = [];
    }

    public function setCarryCoins(int $order, int $carryBet): void
    {
        $this->bettedCoins[$order] = [0,$carryBet];
    }

    public function firstBetInput(Player $player, int $order): void
    {
        while (true) {
            echo $player->getPlayerName() . 'は';
            echo 'いくらベットしますか？(残コイン数：' . $this->getRestCoinAmount($order) . ' ）: ';
            $input = Trim(fgets(STDIN));
            if (!is_numeric($input)) {
                echo '数値を入力してください' . PHP_EOL;
                continue;
            }
            $bet = intval($input);
            if ($bet > 0 && $bet <= $this->getRestCoinAmount($order)) {
                $questionText = $bet . 'コインをベットします。' . PHP_EOL;
                $questionText = $questionText . 'よろしいですか？(Y/N) : ';
                $answer = $this->questionYesNo($questionText);
                if (!$answer) {
                    continue;
                }
                $this->betCoin($order, $bet);
                break;
            }
            echo '残コイン数以内で入力してください' . PHP_EOL;
        }
        echo PHP_EOL;
    }

    public function getBettedCoinAmount(int $order): int
    {
        return $this->bettedCoins[$order][$this::BETTED_CELL];
    }

    public function takeOutBettedAllCoins(int $order): int
    {
        $takeOutCoinAmount = $this->getBettedCoinAmount($order);
        $this->bettedCoins[$order][$this::BETTED_CELL] = 0;
        return $takeOutCoinAmount;
    }

    public function getRestCoinAmount(int $order): int
    {
        return $this->bettedCoins[$order][$this::REST_CELL];
    }

    public function takeOutRestAllCoins(int $order): int
    {
        $takeOutCoinAmount = $this->getRestCoinAmount($order);
        $this->bettedCoins[$order][$this::REST_CELL] = 0;
        return $takeOutCoinAmount;
    }

    public function betCoin(int $order, int $coin): bool
    {
        $nowRestCoin = $this->bettedCoins[$order][$this::REST_CELL];
        try {
            if ($coin < 0) {
                throw new Exception('コイン数が負の値になります');
            } elseif ($coin > $nowRestCoin) {
                throw new Exception('残金以上にベットしようとしています');
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $this->bettedCoins[$order][$this::REST_CELL] -= $coin;
        $this->bettedCoins[$order][$this::BETTED_CELL] += $coin;
        return true;
    }

    public function returnCoin(int $order, int $coin): void
    {
        $nowBettedCoin = $this->bettedCoins[$order][$this::BETTED_CELL];
        try {
            if ($coin < 0) {
                throw new Exception('コイン数が負の値になります');
            } elseif ($coin > $nowBettedCoin) {
                throw new Exception('ベッド数以上に返金しようとしています');
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $this->bettedCoins[$order][$this::REST_CELL] += $coin;
        $this->bettedCoins[$order][$this::BETTED_CELL] -= $coin;
    }

    /**
     * @param array<float> $betMagnifications
     */
    public function calculationReward(array $betMagnifications): void
    {
        foreach (array_keys($betMagnifications) as $order) {
            $rewardCoin = floor($this->takeOutBettedAllCoins($order) * $betMagnifications[$order]);
            $this->bettedCoins[$order][$this::REST_CELL] += $rewardCoin;
        }
    }

    public function questionYesNo(string $questionText): bool
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
}
