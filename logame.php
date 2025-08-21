<?php
session_start();

// ゲームの初期化
if (!isset($_SESSION['game']) || isset($_POST['action']) && $_POST['action'] === 'reset_game') {
    if (isset($_POST['action']) && $_POST['action'] === 'reset_game') {
        session_destroy();
        session_start();
    }
    $_SESSION['game'] = [
        'player' => [
            'hp' => 100,
            'max_hp' => 100,
            'attack' => 10,
            'defense' => 5,
            'position' => 0,
            'level' => 1,
            'exp' => 0,
            'exp_to_next' => 100,
            'gold' => 50,
            'items' => ['回復薬' => 3]
        ],
        'dungeon' => generateDungeon(),
        'current_event' => null,
        'messages' => ['ダンジョンへようこそ！サイコロを振って進みましょう。'],
        'game_over' => false,
        'victory' => false
    ];
}

// ダンジョン生成
function generateDungeon() {
    $events = ['empty', 'monster', 'treasure', 'trap', 'shop', 'healing', 'monster', 'treasure']; // モンスターと宝箱の出現率を少し上げる
    $dungeon = [];
    
    for ($i = 0; $i < 50; $i++) {
        if ($i == 0) {
            $dungeon[] = ['type' => 'start', 'name' => 'スタート'];
        } elseif ($i == 49) {
            $dungeon[] = ['type' => 'boss', 'name' => 'ボス部屋', 'monster' => createBoss()];
        } else {
            $eventType = $events[array_rand($events)];
            $dungeon[] = createEvent($eventType);
        }
    }
    
    return $dungeon;
}

// イベント作成
function createEvent($type) {
    switch ($type) {
        case 'empty':
            return ['type' => 'empty', 'name' => '空き部屋'];
        case 'monster':
            return ['type' => 'monster', 'name' => 'モンスター遭遇', 'monster' => createMonster()];
        case 'treasure':
            return ['type' => 'treasure', 'name' => '宝箱', 'reward' => createTreasure()];
        case 'trap':
            return ['type' => 'trap', 'name' => 'トラップ', 'damage' => rand(5, 15)];
        case 'shop':
            return ['type' => 'shop', 'name' => 'ショップ', 'items' => createShopItems()];
        case 'healing':
            return ['type' => 'healing', 'name' => '回復の泉', 'heal' => rand(20, 40)];
        default:
            return ['type' => 'empty', 'name' => '空き部屋'];
    }
}

// モンスター作成
function createMonster() {
    $monsters = [
        ['name' => 'ゴブリン', 'hp' => 30, 'max_hp' => 30, 'attack' => 8, 'defense' => 2, 'exp' => 20, 'gold' => 10],
        ['name' => 'オーク', 'hp' => 50, 'max_hp' => 50, 'attack' => 12, 'defense' => 4, 'exp' => 35, 'gold' => 20],
        ['name' => 'スケルトン', 'hp' => 40, 'max_hp' => 40, 'attack' => 10, 'defense' => 3, 'exp' => 25, 'gold' => 15],
        ['name' => 'スライム', 'hp' => 25, 'max_hp' => 25, 'attack' => 6, 'defense' => 5, 'exp' => 15, 'gold' => 8],
        ['name' => 'ゴーレム', 'hp' => 70, 'max_hp' => 70, 'attack' => 15, 'defense' => 8, 'exp' => 50, 'gold' => 40],
    ];
    
    return $monsters[array_rand($monsters)];
}

// ボス作成
function createBoss() {
    return [
        'name' => '魔王',
        'hp' => 200,
        'max_hp' => 200,
        'attack' => 25,
        'defense' => 12,
        'exp' => 0, // ボスを倒したらクリアなので経験値はなし
        'gold' => 500
    ];
}

// 宝箱作成
function createTreasure() {
    $treasures = [
        ['type' => 'gold', 'amount' => rand(20, 80)],
        ['type' => 'item', 'name' => '回復薬', 'amount' => rand(1, 2)],
        ['type' => 'item', 'name' => '爆弾', 'amount' => 1],
        ['type' => 'equipment', 'name' => '力の剣', 'stat' => 'attack', 'value' => rand(3, 6)],
        ['type' => 'equipment', 'name' => '鉄の盾', 'stat' => 'defense', 'value' => rand(2, 4)],
        ['type' => 'stat_boost', 'name' => '最大HPアップ', 'stat' => 'max_hp', 'value' => 10],
    ];
    
    return $treasures[array_rand($treasures)];
}

// ショップアイテム作成
function createShopItems() {
    return [
        ['name' => '回復薬', 'price' => 25, 'type' => 'item', 'description' => 'HPを50回復する。'],
        ['name' => '爆弾', 'price' => 60, 'type' => 'item', 'description' => 'モンスターに30ダメージ。'],
        ['name' => '力の薬', 'price' => 80, 'type' => 'stat', 'stat' => 'attack', 'value' => 3, 'description' => '攻撃力が永続的に3上昇。'],
        ['name' => '守りの薬', 'price' => 70, 'type' => 'stat', 'stat' => 'defense', 'value' => 2, 'description' => '防御力が永続的に2上昇。'],
    ];
}

// アクション処理
if ($_POST && !$_SESSION['game']['game_over'] && !$_SESSION['game']['victory']) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'roll_dice':
            rollDice();
            break;
        case 'fight':
            fight();
            break;
        case 'use_item':
            if (isset($_POST['item'])) {
                useItem($_POST['item']);
            }
            break;
        case 'flee':
            flee();
            break;
        case 'buy_item':
            if (isset($_POST['shop_item'])) {
                buyItem($_POST['shop_item']);
            }
            break;
        case 'leave_shop':
            addMessage('ショップから離れた。');
            $_SESSION['game']['current_event'] = null;
            break;
    }
} elseif (isset($_POST['action']) && $_POST['action'] === 'reset_game') {
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// サイコロを振る
function rollDice() {
    if ($_SESSION['game']['current_event'] !== null) return; // イベント中はサイコロを振れない

    $dice = rand(1, 6);
    $newPosition = min($_SESSION['game']['player']['position'] + $dice, 49);
    $_SESSION['game']['player']['position'] = $newPosition;
    
    addMessage("サイコロを振って{$dice}進んだ。 (現在地: {$newPosition})");
    
    $event = $_SESSION['game']['dungeon'][$newPosition];
    $_SESSION['game']['current_event'] = $event;
    
    handleEvent($event);
}

// イベント処理
function handleEvent($event) {
    switch ($event['type']) {
        case 'empty':
            addMessage('ここは空き部屋のようだ。特に何もない。');
            $_SESSION['game']['current_event'] = null;
            break;
        case 'monster':
        case 'boss':
            addMessage($event['monster']['name'] . 'が現れた！');
            break;
        case 'treasure':
            addMessage('宝箱を見つけた！');
            handleTreasure($event['reward']);
            break;
        case 'trap':
            addMessage('トラップだ！');
            handleTrap($event['damage']);
            break;
        case 'shop':
            addMessage('怪しげな商人がいるショップを見つけた。');
            break;
        case 'healing':
            addMessage('回復の泉が湧いている。');
            handleHealing($event['heal']);
            break;
    }
}

// 宝箱処理
function handleTreasure($reward) {
    $player = &$_SESSION['game']['player'];
    switch ($reward['type']) {
        case 'gold':
            $player['gold'] += $reward['amount'];
            addMessage("{$reward['amount']}ゴールドを手に入れた！");
            break;
        case 'item':
            if (!isset($player['items'][$reward['name']])) {
                $player['items'][$reward['name']] = 0;
            }
            $player['items'][$reward['name']] += $reward['amount'];
            addMessage("{$reward['name']}を{$reward['amount']}個手に入れた！");
            break;
        case 'equipment':
            $player[$reward['stat']] += $reward['value'];
            addMessage("{$reward['name']}を見つけた！{$reward['stat']}が{$reward['value']}上がった！");
            break;
        case 'stat_boost':
            $player[$reward['stat']] += $reward['value'];
            if($reward['stat'] === 'max_hp') {
                 $player['hp'] += $reward['value']; // 最大HPが上がった分、現在HPも回復
            }
            addMessage("聖なる遺物だ！{$reward['name']}の効果で{$reward['stat']}が{$reward['value']}上がった！");
            break;
    }
    $_SESSION['game']['current_event'] = null;
}

// トラップ処理
function handleTrap($damage) {
    $player = &$_SESSION['game']['player'];
    $actualDamage = max(1, $damage - $player['defense']);
    $player['hp'] -= $actualDamage;
    addMessage("トラップが作動！{$actualDamage}ダメージを受けた！");
    
    if ($player['hp'] <= 0) {
        $player['hp'] = 0;
        $_SESSION['game']['game_over'] = true;
        addMessage('あなたは力尽きた...');
    }
    
    $_SESSION['game']['current_event'] = null;
}

// 回復処理
function handleHealing($heal) {
    $player = &$_SESSION['game']['player'];
    $oldHp = $player['hp'];
    $player['hp'] = min($player['hp'] + $heal, $player['max_hp']);
    $actualHeal = $player['hp'] - $oldHp;
    
    addMessage("泉の水でHPが{$actualHeal}回復した！");
    $_SESSION['game']['current_event'] = null;
}

// 戦闘処理
function fight() {
    if (!isset($_SESSION['game']['current_event']['monster'])) return;

    $event = &$_SESSION['game']['current_event'];
    $monster = &$event['monster'];
    $player = &$_SESSION['game']['player'];
    
    // プレイヤーの攻撃
    $playerDamage = max(1, $player['attack'] - $monster['defense']);
    $monster['hp'] -= $playerDamage;
    addMessage("あなたの攻撃！{$monster['name']}に{$playerDamage}ダメージを与えた！");
    
    if ($monster['hp'] <= 0) {
        // モンスター撃破
        addMessage("{$monster['name']}を倒した！");
        $player['exp'] += $monster['exp'];
        $player['gold'] += $monster['gold'];
        addMessage("{$monster['exp']}経験値と{$monster['gold']}ゴールドを獲得！");
        
        // レベルアップチェック
        if ($player['exp'] >= $player['exp_to_next']) {
            levelUp();
        }
        
        // ボス撃破チェック
        if ($event['type'] === 'boss') {
            $_SESSION['game']['victory'] = true;
            addMessage('おめでとう！見事、魔王を打ち破りダンジョンを制覇した！');
        }
        
        $_SESSION['game']['current_event'] = null;
        return;
    }
    
    // モンスターの攻撃
    $monsterDamage = max(1, $monster['attack'] - $player['defense']);
    $player['hp'] -= $monsterDamage;
    addMessage("{$monster['name']}の攻撃！あなたは{$monsterDamage}ダメージを受けた！");
    
    if ($player['hp'] <= 0) {
        $player['hp'] = 0;
        $_SESSION['game']['game_over'] = true;
        addMessage('あなたは力尽きた...');
    }
}

// レベルアップ
function levelUp() {
    $player = &$_SESSION['game']['player'];
    while ($player['exp'] >= $player['exp_to_next']) {
        $player['level']++;
        $player['exp'] -= $player['exp_to_next'];
        $player['exp_to_next'] = floor($player['level'] * 100 * 1.2);
        $stat_hp = rand(8, 12);
        $stat_atk = rand(2, 4);
        $stat_def = rand(1, 3);

        $player['max_hp'] += $stat_hp;
        $player['hp'] = $player['max_hp']; // 全回復
        $player['attack'] += $stat_atk;
        $player['defense'] += $stat_def;
        
        addMessage("レベルアップ！レベル{$player['level']}になった！");
        addMessage("HP+{$stat_hp}, 攻撃+{$stat_atk}, 防御+{$stat_def}");
    }
}

// アイテム使用
function useItem($itemName) {
    $player = &$_SESSION['game']['player'];
    
    if (!isset($player['items'][$itemName]) || $player['items'][$itemName] <= 0) {
        addMessage('そのアイテムは持っていない。');
        return;
    }
    
    $used = false;
    switch ($itemName) {
        case '回復薬':
            if ($player['hp'] < $player['max_hp']) {
                $heal = 50;
                $oldHp = $player['hp'];
                $player['hp'] = min($player['hp'] + $heal, $player['max_hp']);
                $actualHeal = $player['hp'] - $oldHp;
                addMessage("回復薬を使った。HPが{$actualHeal}回復した！");
                $used = true;
            } else {
                addMessage('HPは満タンだ。');
            }
            break;
        case '爆弾':
            if (isset($_SESSION['game']['current_event']['type']) && 
               ($_SESSION['game']['current_event']['type'] === 'monster' || $_SESSION['game']['current_event']['type'] === 'boss')) {
                $damage = 30;
                $_SESSION['game']['current_event']['monster']['hp'] -= $damage;
                addMessage("爆弾を投げつけた！{$_SESSION['game']['current_event']['monster']['name']}に{$damage}ダメージ！");
                $used = true;
                // 爆弾で倒した場合の処理
                if ($_SESSION['game']['current_event']['monster']['hp'] <= 0) {
                    fight(); // fight関数で撃破後の処理を共通化
                }
            } else {
                addMessage('モンスターがいないのに爆弾はもったいない！');
            }
            break;
    }
    
    if ($used) {
        $player['items'][$itemName]--;
        if ($player['items'][$itemName] <= 0) {
            unset($player['items'][$itemName]);
        }
    }
}

// 逃走
function flee() {
    if (rand(1, 100) <= 70) {
        addMessage('うまく逃げ切れた！');
        $_SESSION['game']['current_event'] = null;
    } else {
        addMessage('逃走に失敗！');
        // 失敗したらモンスターの攻撃を受ける
        $event = $_SESSION['game']['current_event'];
        $monster = $event['monster'];
        $player = &$_SESSION['game']['player'];
        
        $monsterDamage = max(1, $monster['attack'] - $player['defense']);
        $player['hp'] -= $monsterDamage;
        addMessage("{$monster['name']}の攻撃！{$monsterDamage}ダメージを受けた！");
        
        if ($player['hp'] <= 0) {
            $player['hp'] = 0;
            $_SESSION['game']['game_over'] = true;
            addMessage('あなたは力尽きた...');
        }
    }
}

// アイテム購入
function buyItem($itemIndex) {
    $event = $_SESSION['game']['current_event'];
    $player = &$_SESSION['game']['player'];
    
    if (!isset($event['items'][$itemIndex])) return;
    
    $item = $event['items'][$itemIndex];
    
    if ($player['gold'] < $item['price']) {
        addMessage('ゴールドが足りない！');
        return;
    }
    
    $player['gold'] -= $item['price'];
    
    switch ($item['type']) {
        case 'item':
            if (!isset($player['items'][$item['name']])) {
                $player['items'][$item['name']] = 0;
            }
            $player['items'][$item['name']]++;
            addMessage("{$item['name']}を購入した。");
            break;
        case 'stat':
            $player[$item['stat']] += $item['value'];
            addMessage("{$item['name']}を購入！{$item['stat']}が{$item['value']}上がった！");
            break;
    }
}

// メッセージ追加
function addMessage($message) {
    $_SESSION['game']['messages'][] = $message;
    if (count($_SESSION['game']['messages']) > 15) {
        array_shift($_SESSION['game']['messages']);
    }
}

$game = $_SESSION['game'];
$player = $game['player'];
$currentEvent = $game['current_event'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ローグライクゲーム - サイコロダンジョン</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DotGothic16&display=swap');
        :root {
            --bg-color: #1a1a1a;
            --main-color: #00ff00;
            --accent-color: #ffff00;
            --danger-color: #ff3333;
            --monster-color: #ff6600;
            --ui-bg-color: #2a2a2a;
            --ui-dark-bg-color: #3a3a3a;
            --font-family: 'DotGothic16', 'Courier New', monospace;
        }
        body {
            font-family: var(--font-family);
            background-color: var(--bg-color);
            color: var(--main-color);
            margin: 0;
            padding: 15px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--ui-bg-color);
            border: 2px solid var(--main-color);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
            transition: border-color 0.5s, box-shadow 0.5s;
        }
        h1, h2, h3, h4 {
            color: var(--accent-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        h1 { text-align: center; margin-bottom: 20px; }
        .game-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .player-stats, .dungeon-map, .messages, .inventory, .actions, .event-area {
            background-color: var(--ui-dark-bg-color);
            border: 1px solid var(--main-color);
            padding: 15px;
            border-radius: 5px;
        }
        .bar {
            background-color: #555;
            border: 1px solid var(--main-color);
            height: 20px;
            margin: 5px 0 10px;
            border-radius: 3px;
            overflow: hidden;
        }
        .bar-fill { height: 100%; transition: width 0.5s ease-in-out; }
        .hp-fill { background-color: var(--danger-color); }
        .exp-fill { background-color: #3333ff; }
        .dungeon-progress { position: relative; }
        .player-position {
            position: absolute; top: 50%; transform: translateY(-50%);
            background-color: #ff00ff; width: 20px; height: 20px;
            border-radius: 50%; border: 2px solid #fff;
            transition: left 0.5s ease-in-out;
        }
        .messages {
            grid-column: 1 / -1;
            height: 180px;
            overflow-y: auto;
            display: flex;
            flex-direction: column-reverse;
        }
        .message { margin: 2px 0; padding: 2px 5px; border-bottom: 1px dotted #444; }
        .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        button {
            background-color: var(--main-color); color: var(--bg-color);
            border: none; padding: 10px 20px; border-radius: 5px;
            cursor: pointer; font-family: var(--font-family);
            font-weight: bold; transition: all 0.2s ease;
            box-shadow: 0 3px #009900;
        }
        button:hover { background-color: #33ff33; transform: translateY(-2px); box-shadow: 0 5px #009900; }
        button:active { transform: translateY(1px); box-shadow: 0 2px #009900; }
        button:disabled {
            background-color: #666; cursor: not-allowed;
            transform: none; box-shadow: 0 3px #444;
        }
        .event-area { border-color: var(--monster-color); }
        .monster-stats {
            background-color: #4a2a2a; border: 1px solid var(--monster-color);
            padding: 15px; border-radius: 5px; margin: 10px 0;
        }
        .monster-hp-fill { background-color: var(--monster-color); }
        .shop-items { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        .shop-item {
            background-color: var(--ui-bg-color); border: 1px solid var(--main-color);
            padding: 10px; border-radius: 5px; text-align: center;
        }
        .shop-item p { margin: 5px 0; font-size: 0.9em; color: #ccc; }
        .items-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; }
        .item-slot {
            background-color: var(--ui-bg-color); border: 1px solid #666;
            padding: 10px; border-radius: 3px; text-align: center;
        }
        .item-slot button { padding: 5px 10px; font-size: 0.9em; width: 100%; margin-top: 5px; }
        .game-over-screen, .victory-screen {
            padding: 40px; border-radius: 5px; text-align: center;
        }
        .game-over-screen { background-color: #4a1a1a; border: 2px solid var(--danger-color); }
        .victory-screen { background-color: #1a4a1a; border: 2px solid var(--main-color); }
        .reset-button { background-color: var(--monster-color) !important; box-shadow: 0 3px #cc5200 !important; }
        .reset-button:hover { box-shadow: 0 5px #cc5200 !important; }
        .reset-button:active { box-shadow: 0 2px #cc5200 !important; }
        @media (max-width: 800px) {
            .game-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            button { width: 100%; }
        }
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
        .shake-effect { animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both; }
    </style>
</head>
<body>
    <div class="container" id="game-container">
        <h1>🎲 ローグライクゲーム - サイコロダンジョン 🎲</h1>
        
        <?php if ($game['victory']): ?>
            <div class="victory-screen">
                <h2>🏆 勝利！ 🏆</h2>
                <p>おめでとうございます！あなたは魔王を倒し、ダンジョンの謎を解き明かしました！</p>
                <p>あなたの最終ステータス: レベル <?= $player['level'] ?>, ゴールド <?= $player['gold'] ?></p>
                <form method="post">
                    <button type="submit" name="action" value="reset_game" class="reset-button">新しい冒険を始める</button>
                </form>
            </div>
        <?php elseif ($game['game_over']): ?>
            <div class="game-over-screen">
                <h2>💀 ゲームオーバー 💀</h2>
                <p>あなたは力尽きました... しかし、あなたの冒険は伝説として語り継がれるでしょう。</p>
                <p>到達階層: <?= $player['position'] ?>/49, 最終レベル: <?= $player['level'] ?></p>
                <form method="post">
                    <button type="submit" name="action" value="reset_game" class="reset-button">もう一度挑戦する</button>
                </form>
            </div>
        <?php else: ?>
            
            <div class="game-grid">
                <div class="player-stats">
                    <h3>🧙‍♂️ プレイヤー情報</h3>
                    <p><strong>レベル:</strong> <?= $player['level'] ?></p>
                    <p><strong>HP:</strong> <?= $player['hp'] ?> / <?= $player['max_hp'] ?></p>
                    <div class="bar hp-bar"><div class="hp-fill" style="width: <?= ($player['hp'] / $player['max_hp']) * 100 ?>%"></div></div>
                    <p><strong>経験値:</strong> <?= $player['exp'] ?> / <?= $player['exp_to_next'] ?></p>
                    <div class="bar exp-bar"><div class="exp-fill" style="width: <?= ($player['exp'] / $player['exp_to_next']) * 100 ?>%"></div></div>
                    <p><strong>攻撃力:</strong> <?= $player['attack'] ?> | <strong>防御力:</strong> <?= $player['defense'] ?></p>
                    <p><strong>ゴールド:</strong> <?= $player['gold'] ?> G</p>
                </div>
                
                <div class="dungeon-map">
                    <h3>🗺️ ダンジョン進行状況</h3>
                    <p><strong>現在地:</strong> <?= $player['position'] ?> / 49</p>
                    <div class="bar dungeon-progress">
                        <div class="bar-fill" style="background-color: var(--accent-color); width: <?= ($player['position'] / 49) * 100 ?>%"></div>
                        <div class="player-position" style="left: calc(<?= ($player['position'] / 49) * 100 ?>% - 10px);"></div>
                    </div>
                    <p><strong>現在のマス:</strong> <?= $game['dungeon'][$player['position']]['name'] ?></p>
                </div>

                <div class="messages">
                    <div> <!-- このdivがスクロールの起点 -->
                        <?php foreach (array_reverse($game['messages']) as $message): ?>
                            <div class="message"><?= htmlspecialchars($message) ?></div>
                        <?php endforeach; ?>
                        <h3 style="text-align:center; margin: 10px 0;">📜 メッセージログ 📜</h3>
                    </div>
                </div>

                <div class="inventory" style="grid-column: 1 / -1;">
                    <h3>🎒 インベントリ</h3>
                    <?php if (empty($player['items'])): ?>
                        <p>アイテムを持っていません。</p>
                    <?php else: ?>
                        <div class="items-grid">
                            <?php foreach ($player['items'] as $itemName => $count): ?>
                                <div class="item-slot">
                                    <div><?= htmlspecialchars($itemName) ?> ×<?= $count ?></div>
                                    <form method="post" style="margin: 0;">
                                        <input type="hidden" name="item" value="<?= htmlspecialchars($itemName) ?>">
                                        <button type="submit" name="action" value="use_item" title="アイテムを使用します (キー: i)">使用</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($currentEvent): ?>
                <div class="event-area">
                    <h3>🎭 イベント: <?= htmlspecialchars($currentEvent['name']) ?></h3>
                    
                    <?php if ($currentEvent['type'] === 'monster' || $currentEvent['type'] === 'boss'): ?>
                        <div class="monster-stats">
                            <h4>👹 <?= htmlspecialchars($currentEvent['monster']['name']) ?></h4>
                            <p><strong>HP:</strong> <?= $currentEvent['monster']['hp'] ?> / <?= $currentEvent['monster']['max_hp'] ?></p>
                            <div class="bar monster-hp-bar"><div class="monster-hp-fill" style="width: <?= ($currentEvent['monster']['hp'] / $currentEvent['monster']['max_hp']) * 100 ?>%"></div></div>
                            <p><strong>攻撃力:</strong> <?= $currentEvent['monster']['attack'] ?> | <strong>防御力:</strong> <?= $currentEvent['monster']['defense'] ?></p>
                        </div>
                        <div class="actions">
                            <div class="action-buttons">
                                <form method="post"><button type="submit" name="action" value="fight" title="モンスターを攻撃します (キー: f)">⚔️ 攻撃</button></form>
                                <form method="post"><button type="submit" name="action" value="flee" title="戦闘から逃走します (キー: r)">🏃‍♂️ 逃走</button></form>
                            </div>
                        </div>
                    <?php elseif ($currentEvent['type'] === 'shop'): ?>
                        <div class="shop-items">
                            <?php foreach ($currentEvent['items'] as $index => $item): ?>
                                <div class="shop-item">
                                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                                    <p><?= htmlspecialchars($item['description']) ?></p>
                                    <div><strong>価格: <?= $item['price'] ?> G</strong></div>
                                    <form method="post">
                                        <input type="hidden" name="shop_item" value="<?= $index ?>">
                                        <button type="submit" name="action" value="buy_item" <?= $player['gold'] < $item['price'] ? 'disabled' : '' ?>>購入</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                         <div class="actions" style="margin-top:15px;">
                            <form method="post"><button type="submit" name="action" value="leave_shop">ショップから出る</button></form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="actions">
                    <h3>🎯 アクション</h3>
                    <div class="action-buttons">
                        <form method="post">
                            <button type="submit" name="action" value="roll_dice" title="サイコロを振って進みます (Enter)">🎲 サイコロを振る</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <form method="post">
                    <button type="submit" name="action" value="reset_game" class="reset-button">🔄 ゲームリセット</button>
                </form>
            </div>

        <?php endif; ?>
    </div>
    
    <script>
        // メッセージログの自動スクロール
        const messagesEl = document.querySelector('.messages');
        if (messagesEl) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
        
        // HP低下時の視覚効果
        const hpPercentage = <?= ($player['hp'] / $player['max_hp']) * 100 ?>;
        const containerEl = document.getElementById('game-container');
        if (hpPercentage < 25) {
            containerEl.style.borderColor = 'var(--danger-color)';
            containerEl.style.boxShadow = '0 0 20px rgba(255, 0, 0, 0.5)';
        } else if (hpPercentage < 50) {
            containerEl.style.borderColor = 'var(--monster-color)';
            containerEl.style.boxShadow = '0 0 20px rgba(255, 136, 0, 0.3)';
        }

        // キーボードショートカット
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            let buttonToClick = null;
            switch(e.key) {
                case 'Enter':
                    buttonToClick = document.querySelector('button[value="roll_dice"]');
                    break;
                case 'f':
                case 'F':
                    buttonToClick = document.querySelector('button[value="fight"]');
                    break;
                case 'r':
                case 'R':
                    buttonToClick = document.querySelector('button[value="flee"]');
                    break;
                case 'i':
                case 'I':
                    // 複数の使用ボタンがあるため、最初のものをクリック
                    buttonToClick = document.querySelector('button[value="use_item"]');
                    break;
            }

            if (buttonToClick && !buttonToClick.disabled) {
                e.preventDefault();
                buttonToClick.click();
            }
        });
    </script>
</body>
</html>