<?php 
  ini_set('log_errors','on');
  ini_set('error_log','php.log');
  session_start();

  $heroes = array();  // モンスター達の格納用
  $monsters = array();  // モンスター達の格納用
  

  // 抽象クラス（登場人物 Characterクラスとして他のクラスの設計図）----------------------------------------
  abstract class Character{  // abstract を最初につけるだけ
    protected $name;          // 継承されるので private にしない
    protected $hp;
    protected $img;
    protected $attackMin;
    protected $attackMax;

    abstract public function doSth($sOne, $other);   // 抽象メソッド(継承先で必ず使う必要があるメソッド) abstractを付けるだけ

    public function setName($str){    // クラスの外からプロパティにアクセスするためのセッターとゲッター 
      $this->name = $str;
    }
    public function getName(){        // 変数の値を使う場合
      return $this->name;
    }
    public function setHp($num){      // 引数を受けて、変数の値を変える場合はセッターを使う = セット（設定）する
      $this->hp = $num;
    }
    public function getHp(){
      return $this->hp;
    }
    public function setImg($str){
      $this->img = $str;
    }
    public function getImg(){
      return $this->img;
    }

    public function attack($target){   // 便利メソッドの強制継承(継承先でオーバーライドは可能！)
      History::set($this->name.'の攻撃！');

      $attackPoint = mt_rand($this->attackMin, $this->attackMax);
      if(!mt_rand(0, 9)){
        $attackPoint *= 1.5;
        $attackPoint = (int)$attackPoint;
        History::set('会心の一撃！！');
      }
      $target->setHp($target->getHp() - $attackPoint);
      if($target == $_SESSION['monster']){
        History::set($attackPoint.'のダメージを与えた！');
      }else{
        History::set($attackPoint.'のダメージを受けた！');
      }
      if($target->getHp() < 0) $target->setHp(0);
 
    }
  }

  abstract class SubCharacter{   // 抽象クラス（脇役登場人物 subCharacterクラスとして他のクラスの設計図）
    protected $name;
    protected $hp;
    protected $img;

    public function setName($str){ 
      $this->name = $str; 
    }
    public function getName(){    
      return $this->name;
    }
    public function getHp(){
      return $this->hp;
    }
    public function setImg($str){
      $this->img = $str;
    }
    public function getImg(){
      return $this->img;
    }

    abstract public function heal($obj);
  }

  //  抽象クラス Character を継承を継承した親クラスの定義 --------------------------------------------------------------------------
  class Hero extends Character{   // extends で抽象クラスを継承する
    protected $specialMin;        // 親クラスなので、継承される可能性がある - protected にする
    protected $specialMax;
    protected $special;
    protected $agility;

    public function __construct($name, $hp, $img, $attackMin, $attackMax, $specialMin, $specialMax, $special, $agility){
      
      //parent::__construct($name, $hp, $img, $attackMin, $attackMax); ”抽象クラスでコンストラクトしないので、コンストラクタの継承はない”
      $this->name = $name;
      $this->hp = $hp;
      $this->img = $img;
      $this->attackMin = $attackMin;
      $this->attackMax = $attackMax;
      $this->specialMin = $specialMin;
      $this->specialMax = $specialMax;
      $this->special = $special;
      $this->agility = $agility;
    }

    public function getAgility(){
      return $this->agility;
    }

    public function attack($target){        // オーバーライド attackメソッドをこのクラス用にカスタマイズ
      if(!empty($_POST['special'])){
        History::set($this->name.'が'.$this->special.'を繰り出した！！');
        if(!mt_rand(0, 3)){
          History::set('攻撃は失敗した！');
        }else{
          $attackPoint = mt_rand($this->specialMin, $this->specialMax);
          $target->setHp($target->getHp() - $attackPoint);
          History::set($attackPoint.'のダメージを与えた！');
          if($target->getHp() < 0) $target->setHp(0);
        }
      }else{
        parent::attack($target);          // 抽象クラスのメソッドをそのまま使用 - 簡単に parent:: で使う
      }
    }

    public function doSth($sOne, $other){  // 定義されていた抽象メソッド - まだ未開発

    }
  }

  class Monster extends Character{
    protected $distance;

    public function __construct($name, $hp, $img, $attackMin, $attackMax, $distance){
      $this->name = $name;
      $this->hp = $hp;
      $this->img = $img;
      $this->attackMin = $attackMin;
      $this->attackMax = $attackMax;
      $this->distance = $distance;
    }

    public function doSth($sOne, $other){             // 定義されていた抽象メソッドを使用
      if($sOne->getName() === '黒いサンタ'){
        History::set($sOne->getName().'はプレゼントの薬草をくれた！');
        $other->setHp($other->getHp() + 300);
        History::set($other->getName().'の体力が少し回復！');
      }

    }

    public function giveDistance($totalDistance){     // このクラス独自のメソッド
      return $totalDistance = $totalDistance + $this->distance;
    }

    public function setDistance($num){
      $this->distance = $num;
    }
    public function getDistance(){
      return $this->distance;
    }
  }
  // 親クラス Monster を継承した子クラスを定義 ----------------------------------------------------------------
  class magicMonster extends Monster{   
    private $magicMin;
    private $magicMax;

    public function __construct($name, $hp, $img, $attackMin, $attackMax, $magicMin, $magicMax, $distance){
      parent::__construct($name, $hp, $img, $attackMin, $attackMax, $distance);   // 親クラスのコンストラクタを継承
      $this->magicMin = $magicMin;
      $this->magicMax = $magicMax;
    }

    public function attack($target){   // オーバーライド attackメソッドをこのクラス用にカスタマイズ
      if(!empty($_POST['special'])){
        History::set($this->name.'が魔法の攻撃！！');
        $attackPoint = mt_rand($this->magicMin, $this->magicMax);
        $target->setHp($target->getHp() - $attackPoint);
        History::set($attackPoint.'のダメージ！');
        if($target->getHp() < 0) $target->setHp(0);
      }else{
        parent::attack($target); // 抽象クラスのメソッドをそのまま使用 - 簡単に parent:: で使う
      }
    }

  }

  // 抽象クラス SubCharacter を継承した親クラスを定義 -----------------------------------------------------------------
  class Friend extends SubCharacter{
    public function __construct($name, $hp, $img){
      $this->name = $name;
      $this->hp = $hp;
      $this->img = $img; 
    }
    
    public function heal($obj){
        History::set('オイオイ、オレだよ。沙悟浄だよ');
        History::set('沙悟浄がきゅうりを分けてくれた');
        $obj->setHp($obj->getHp() + 400);
        History::set($obj->getName().'の体力が一気に戻って元気が出た！');
        unset($_SESSION['friend']); 
        History::set('沙悟浄はその場から立ち去った');
        debug('沙悟浄がきゅうりを渡す');
    }
  }

  // 単発のクラス History の中で 静的メンバ(プロパティまたはメソッド）またの名を "staticメンバ"（インスタンスの生成なしで使える）を定義 "クラスの中で定義されたプロパティとメソッドはそれぞれのインスタンスにくっついているが、staticメンバはクラスにくっついている クラス名::$プロパティ（メソッド名）で呼び出せる" ----------------------------------------------------------------------------------------------
  class History{
    public static function set($str){     // 静的メンバ (すぐに使える！) - 関数をstaticとしてあるクラスにまとめたようなもの クラス名::関数名(); としてそれらを簡単に使える
      if(empty($_SESSION['history'])) $_SESSION['history'] = '';
      $_SESSION['history'] .= $str.'<br>';
    }
    public static function clear(){       // 静的メンバ
      unset($_SESSION['history']);
    }
  }

  // インスタンスの生成 ---------------------------------------------------------------------------
  $heroes[] = new Hero('孫悟空', 500, 'img/hero01.png', 40, 100, 80, 130, '如意棒', 7);
  $heroes[] = new Hero('猪八戒', 600, 'img/hero02.png', 50, 120, 70, 150, '怪力パンチ', 4);
  $friends[] = new friend('沙悟浄', ' ', 'img/friend01.png');
  $monsters[] = new Monster('一反もめん', 80, 'img/monster01.png', 20, 30, 3000);
  $monsters[] = new Monster('ゾンビ', 100, 'img/monster02.png', 30, 40, 6000);
  $monsters[] = new magicMonster('魔女', 100, 'img/monster03.png', 10, 20, 70, 100, 12000);
  $monsters[] = new Monster('赤鬼', 180, 'img/monster04.png', 30, 60, 12000);
  $monsters[] = new Monster('半魚人', 180, 'img/monster05.png', 40, 60, 15000);
  $monsters[] = new magicMonster('黒いサンタ', 180, 'img/monster06.png', 20, 30, 70, 120, 16000);
  $monsters[] = new Monster('オーク', 300, 'img/monster07.png', 50, 80, 18000);
  $monsters[] = new magicMonster('ヤマタノオロチ', 400, 'img/monster08.png', 50, 80, 80, 140, 20000);
  $monsters[] = new magicMonster('牛魔王', 600, 'img/monster09.png', 10, 120, 100, 200, 50000);
  
  // 関数の定義 ------------------------------------------------------------------------------------
  
  function createHero(){
    global $heroes;

    $_SESSION['hero'] = $heroes[$_POST['hero']];  // POST送信で選択された値(ここでは０か1)を$heroesのキーとして使い $heroesインスタンスの一つを取得、 そのインスタンスをセッション変数 $_SESSION['hero'] に代入する
    debug('選択されたキャラ：'.print_r($_SESSION['hero'], true));
  }

  function createMonster($stage){ // 変数$stage を引数で取得し、ステージごとにモンスターを割り振る。それらのモンスターをランダムでセッション変数 $_SESSION['monster'] に代入する
    global $monsters;

    if($stage == 'first' || empty($stage)){
      $_SESSION['monster'] = $monsters[mt_rand(0, 3)]; // セッションに格納
    }elseif($stage == 'second'){
      $_SESSION['monster'] = $monsters[mt_rand(4, 7)]; // セッションに格納
    }elseif($stage == 'third'){
      $_SESSION['monster'] = $monsters[8];
    }
   
    debug('現れたモンスター：'.print_r($_SESSION['monster'], true));
    History::set($_SESSION['monster']->getName().'が現れた！');
  
  }

  function createFriend(){  // Friendクラスのインスタンスを呼び出す
    global $friends;

    $_SESSION['friend'] = $friends[0];      // $_SESSION['friend']を定義 - ここでセッションにインスタンスを格納
    debug('現れた仲間：'.print_r($_SESSION['friend'], true));
    History::set($_SESSION['friend']->getName().'が現れた！');
    

  }

  function init(){      // スタート、リスタート時にそれぞれの値を準備、用意する
    global $stage;

    History::clear();  // Historyの初期化
    History::set('いざ天竺へ出発！！');
    debug('historyを初期化しました');

    $_SESSION['distance'] = 0; // $_SESSION['distance']を定義

    createHero();           // hero を用意
    createMonster($stage);  // monster を用意
    debug('天竺への距離、選択されたキャラ、最初のモンスターの準備ができました');
  }

  function gameOver(){
    if(empty($_SESSION['gameover'])) $_SESSION['gameover'] = '';
    $_SESSION['gameover'] = 'GAME OVER';      //  ゲームオーバーセッションを用意

  }

  function debug($str){     // ディバッグ用の関数
    error_log($str);
  }


  // POST送信がない場合の画面表示の準備 -------------------------------------------------------------------------------------
  if(empty($_SESSION['distance'])) $_SESSION['distance'] = 0;
 

  if($_SESSION['distance'] < 20000){    // $_SESSION['distance'] の値によって 変数$stage にそれぞれの値を入れてステージを変更する
    $stage = "first";
  }elseif($_SESSION['distance'] < 70000 && $_SESSION['distance'] >= 20000){
    $stage = "second";
  }elseif($_SESSION['distance'] < 108000 && $_SESSION['distance'] >= 70000){
    $stage = "third";
  }else{
    $stage = "ending";
  }

  // 画面表示処理 -------------------------------------------------------------------------------------
  if(!empty($_POST)){
    $startFlg = (!empty($_POST['start'])) ? true : false;  // 便利な flg を用意
    $attackFlg = (!empty($_POST['attack'])) ? true : false;
    $specialFlg = (!empty($_POST['special'])) ? true : false;
    $runFlg = (!empty($_POST['run'])) ? true : false;
    debug("\n".'POST送信あり startFlg: '.$startFlg.' attackFlg: '.$attackFlg.' specialFlg: '.$specialFlg.' run: '.$runFlg);

    if(empty($_POST['restart'])){   // リスタートではない場合 - スタートとリスタートで処理が違うので
      if($startFlg){                        // スタートボタンの場合
        History::set('いざ天竺へ出発！！');
        init();  // 全て必要なの数値を用意
      }else{                                // その他の戦闘時の選択肢
        debug('heros hp'.$_SESSION['hero']->getHp());
        debug('heros :img'.$_SESSION['hero']->getImg());
        debug('heros agility:'.$_SESSION['hero']->getAgility());
        debug('session: '.print_r($_SESSION, true));
        History::clear();
       
       
        if(!empty($_SESSION['friend'])){    // 沙悟浄の存在チェック
          if($attackFlg || $specialFlg || $runFlg){
            $_SESSION['friend']->heal($_SESSION['hero']);
          }
          
        }elseif($attackFlg || $specialFlg){  // 攻撃か得意技 を選択した場合
        
          if(mt_rand(0, 9) <= $_SESSION['hero']->getAgility()){   // Heroの agility の値によってイニシアチブの決定
            
            $_SESSION['hero']->attack($_SESSION['monster']);
            debug('heroの攻撃');
            if($_SESSION['monster']->getHp() <= 0){               // 体力が０になった時点で次の処理（それぞれの攻撃時に確認）
              History::set($_SESSION['monster']->getName().'を倒した！');   
              $_SESSION['distance'] = $_SESSION['monster']->giveDistance($_SESSION['distance']);
              $_SESSION['monster']->doSth($_SESSION['monster'], $_SESSION['hero']);

            }else{
              $_SESSION['monster']->attack($_SESSION['hero']);
              debug('monsterの攻撃');
              if($_SESSION['hero']->getHp() <= 0){
                History::set($_SESSION['hero']->getName().'は倒された！');
              }
            }
          }else{
            $_SESSION['monster']->attack($_SESSION['hero']);    // 後攻めになった場合
            debug('monsterの攻撃');
            if($_SESSION['hero']->getHp() <= 0){
              History::set($_SESSION['hero']->getName().'は倒された！');
            }else{

              $_SESSION['hero']->attack($_SESSION['monster']);
              debug('heroの攻撃');
              if($_SESSION['monster']->getHp() <= 0){
                History::set($_SESSION['monster']->getName().'を倒した！');
                $_SESSION['distance'] = $_SESSION['monster']->giveDistance($_SESSION['distance']);
                $_SESSION['monster']->doSth($_SESSION['monster'], $_SESSION['hero']);
              }
            }
          }
          
        }else{                                                        // 逃げる を選択した場合
          History::set($_SESSION['monster']->getName().'から逃げた！');
          createMonster($stage);     
        }

        if($_SESSION['hero']->getHp() <= 0){    // Hero の体力がなくなった場合 = ゲームオーバー
          gameOver();
          //debug('session gameover: '.$_SESSION['gameover']);
        }elseif($_SESSION['monster']->getHp() <= 0 && empty($_SESSION['friend']) && $_SESSION['monster']->getName() !== '牛魔王'){    // モンスターが死んだ場合に次のモンスターを呼ぶ
          if(!mt_rand(0, 4)){      // 1/5の確率で沙悟浄を登場させる
            createFriend();
          }else{

            createMonster($stage);    // そうでなければ次のモンスターを呼ぶ
          } 
        }
        $stage;

        if($_SESSION['distance'] < 20000){
          $stage = "first";
        }elseif($_SESSION['distance'] < 70000 && $_SESSION['distance'] >= 20000){
          $stage = "second";
        }elseif($_SESSION['distance'] < 108000 && $_SESSION['distance'] >= 70000){
          $stage = "third";
        }else{
          $stage = "ending";
        }
      }   
      debug('POSTを初期化します');
      $_POST = array();
      debug('stage: '.$stage);
    }else{
      debug('セッションを初期化します');
      $_SESSION = array();            // 全てのセッションをリセット (または session_destroy() でもOK) unset($_SESSION['gameover']); unsetは特定のセッションの削除に使う
      debug('リスタート、トップ画面へ');
    }
  }else{
    $_SESSION = array();

  }

  

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>journey to the west</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=M+PLUS+1p&display=swap" rel="stylesheet">
  <style>


  </style>
</head>

<body>
  <div class="wrapper">

    <?php if(empty($_SESSION) || $stage === 'ending'): ?>
          <div class="background-container" style="background: url('img/bg01.jpg'); background-size:1000px 750px;">
            <div style="width:500px; height:450px; margin: 0 auto; padding-top:100px">
              <div style="font-size:48px; color: rgba(255, 0, 0, 0.658); float:left">西遊記伝</div>
              <img src="<?php echo (!empty($stage) && $stage === 'ending') ? "img/bk_ending.png" : "img/friend02.png" ?> " alt="" style="width:400px; height:300px; display:block; margin-left:100px;">

              <label style="font-size:50px; font-weight:bold; line-height:150%; color:rgba(255, 0, 0, 0.658); text-align:center; position:absolute; left:500px; top:250px; z-index:3;">
                <?php echo (!empty($stage) && $stage === "ending") ? "牛魔王を倒した！！<br> 天竺に到着しました！ <br> めでたし めでたし！": ''; ?>
              </label>
            
              <form method="post" style="margin-top:30px; ">
                <input type="radio" name="hero" value="0" id="goku" checked> <!--ここのidとlabelのforを同じにすること-->
                <label for="goku" class=label>孫悟空</label>
                <input type="radio" name="hero" value="1" id="hakkai"> 
                <label for="hakkai" class="label">猪八戒</label>
                <input type="submit" name="start" value="ゲームスタート" style="margin:5px; float:right">
              </form>
            </div>
          </div>

    <?php else: ?>

    <div class="<?php echo (!empty($stage)) ? $stage : "first";?>">

      <div class="clearfix">
        <h1>西遊記伝</h1>
        <ul>
          <li class="lit">平頂山~ </li>
          <li class="<?php if(!empty($_SESSION['distance']) && $_SESSION['distance'] >= 30000) echo "lit" ?>">落胎泉~ </li>
          <li class="<?php if(!empty($_SESSION['distance']) && $_SESSION['distance'] >= 90000) echo "lit" ?>">火焔山~ </li>
          <li>天竺</li>
        </ul>
      </div>

      <label style="font-size:100px; font-weight:bold; color:rgba(255, 208, 0, 0.7); text-align:center; position:absolute; left:400px; top:400px; z-index:3;">
        <?php echo (!empty($_SESSION['gameover'])) ? $_SESSION['gameover'] : ''; ?>
      </label>
     
      <div class="left-top">
        <div class="name"></div>
        <div class="img-container">
          <img src="<?php if(!empty($_SESSION['friend'])) {echo $_SESSION['friend']->getImg();}elseif(!empty($_SESSION['monster'])) {echo $_SESSION['monster']->getImg();}else{echo '';} ?>" alt="" style="width:400px; height:300px;">
        </div>
        <div class="info-container">
          <div class="name">
          <?php if(!empty($_SESSION['friend'])) {echo $_SESSION['friend']->getName();}elseif(!empty($_SESSION['monster'])) {echo $_SESSION['monster']->getName();} ?> 
          </div>
          <div class="hp" style="<?php if(!empty($_SESSION['monster']) && $_SESSION['monster']->getHp() < 50) echo "color:red;"; ?>">
            体力: <?php if(!empty($_SESSION['friend'])) {echo $_SESSION['friend']->getHp();}elseif(!empty($_SESSION['monster'])) {echo $_SESSION['monster']->getHp();} ?>
          </div>
        </div>
      </div>
      
      <div class="right-top">
        <div class="name"></div>
        <div class="img-container">
          <img src="<?php if(!empty($_SESSION['hero'])) echo $_SESSION['hero']->getImg(); ?>" alt="" style="width:400px; height:300px;">
        </div>
        <div class="info-container">
        <div class="name" style="width:230px;">
            <?php if(!empty($_SESSION['hero'])) echo $_SESSION['hero']->getName(); ?>
          </div>
          <div class="hp" style="width:170px; <?php if(!empty($_SESSION['hero']) && $_SESSION['hero']->getHp() < 100) echo "color:red;"; ?>">
            体力: <?php if(!empty($_SESSION['hero'])) echo $_SESSION['hero']->getHp(); ?>
          </div>
        </div>
      </div>

      <div class="left-bottom">
        <p><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
      </div>
      <div class="right-bottom">
        <div class="display">
          <div class="action" style="width:50%; height:70px; float:left;">
            <div style="width:100%; height:40%;">
              アクション
            </div>
            <div style="wodth:100%; height:60%; text-align:center; font-size:20px;">
              得意技を使った
            </div> 
          </div>
          <div class="total-distance" style="width:50%; height:70px; float:right;">
            <div style="width:100%; height:40%;">
              ここまで旅した距離
            </div>
            <div style="wodth:100%; height:60%; text-align:right; font-size:20px; margin-right:50px;">
              <?php echo (!empty($_SESSION['distance'])) ? $_SESSION['distance'] : '0'; ?> 千里
            </div>    
          </div>
        </div>
        <div class="buttons-container">
          <form action="" method="post">
            <input type="submit" name="attack" value="攻撃する" style="border-bottom:none; border-right:none;">
            <input type="submit" name="special" value="得意技を使う" style="float:right; border-bottom:none;">
            <input type="submit" name="run" value="敵から逃げる" style="border-right:none;">
            <input type="submit" name="restart" value="ゲームのリセット" style="float:right">
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <!-- <div class="footer"></div> -->
  </div>
</body>

</html>