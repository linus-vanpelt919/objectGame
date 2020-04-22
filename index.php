<?php
//ログの吐き出し
  ini_set('log_errors','On');
  ini_set('error_log','php.log');
//セッションスタート
session_start();
// モンスター格納用
$monsters = array();
//デバック関数
$debugFlg = true;
function debug($str){
    global $debugFlg;
    if(!empty($debugFlg)){
          error_log('デバッグ: '.$str);
      }
}
//定数
class Sex{
    const MAN = 1;
    const WOMAN = 2;
    const FUMEI = 3;
}
//インターフェイス インスタンスを作成するほどのものでもない機能の実装を強制する(漏れを防ぐ)　
interface HistoryInterface{//セッションを作成し文字を表示、または空にする関数
    public static function set($str);
    public static function clear();
    public static function resetHistory();
}
class History implements HistoryInterface{
    public static function set($str){
        if(empty($_SESSION['history']))
            $_SESSION['history'] = '';
        $_SESSION['history'] .= $str.'<br>'; //できれば一行だけ表示したい
    }
    public static function clear(){
        debug('セッション[history]を初期化します');
        unset($_SESSION['history']);
    }
    public static function resetHistory(){
        if($_SESSION['history']) $_SESSION['history'] = '';
    }
}

//オブジェクト作成
//(抽象クラス) //共通している処理はpublic function 同じ関数でも違う処理にしたいときにabstractをつける
abstract class Creature {
    protected $name;
    protected $hp;
    protected $attackMin;
    protected $attackMax;
    //コンストラクタ
   public function __construct($name,$hp,$attackMin,$attackMax) {
         $this->name = $name;
         $this->hp = $hp;
         $this->attackMax = $attackMax;
         $this->attackMin = $attackMin;
   }
   //このabstractは処理は各々任せたから必ず実装してねという意味
   abstract public function sayCry();
   //セッタ- ゲッター
    public function setHp($num){
       return $this->hp = $num;
    }
    public function getHP(){
       return $this->hp;
    }
    //名前は読み取り専用
    public function getName(){
       return $this->name;
    }
    public function attack($targetObj){
        $attackPoint = mt_rand($this->attackMin,$this->attackMax);
       //５分の１でクリティカル
       if(!mt_rand(0,4)){
           debug('クリティカルヒット!!');
           $attackPoint = $attackPoint * 1.5;
           debug('$attackPointの中身'.print_r($attackPoint,true));
           $attackPoint = (int)$attackPoint;
           debug('$attackPointの中身(int後)'.print_r($attackPoint,true));
           History::set($this->getName().'のクリティカルヒット!!');
         }else{
           debug('通常攻撃!!');
       }
          $targetObj->setHp($targetObj->getHp() -$attackPoint);
          History::set($targetObj->getName().'に'.$attackPoint.'のダメージ');
          History::set($targetObj->sayCry());
    }
}
//(勇者クラス)性別と呪文を追加
class Human extends Creature{
   protected $sex;
   protected $jyumon;
   public function __construct($name, $hp, $attackMin, $attackMax,$sex,$jyumon) {
       parent::__construct($name, $hp, $attackMin, $attackMax);
       $this->sex = $sex;
       $this->jyumon = $jyumon;
   }
   public function sayCry() {
      switch ($this->sex){
          case Sex::MAN:
           History::set('ぐはあっ');
              break;
          case Sex::WOMAN:
           History::set('きゃっ');
              break;
          case Sex::FUMEI:
            History::set('ふふ・・もっと♡');
              break;
      }
   }
   public function Jyumon($targetObj) {
       $attackPoint = $this->jyumon;
       if($_SESSION['jyumon'] >= 5){
           debug('勇者の魔法攻撃!!');
           History::set('勇者の魔法攻撃');
           History::set($targetObj->getName().'へ魔法攻撃!!');
           History::set($targetObj->sayCry());
           $targetObj->setHp($targetObj->getHp() - $attackPoint);
           $_SESSION['jyumon'] -= 5;
       }else{
           debug('MPが足りません');
           History::set('勇者の魔法攻撃・・・しかしMPが足りない!!');
       }
   }
}

class Monster extends Creature{
    protected $img;
    public function __construct($name, $hp, $attackMin, $attackMax,$img) {
        parent::__construct($name, $hp, $attackMin, $attackMax);
        $this->img =$img;
    }
    public function getImg(){
        return $this->img;
    }
    public function sayCry() {
        History::set($this->name.'が叫ぶ!');
        History::set('ぐぎぎぎぎ');
    }
}
class MagicMonster extends Monster {
    private $magicAttack;
    public function __construct($name, $hp, $attackMin, $attackMax, $img,$magicAttack) {
        parent::__construct($name, $hp, $attackMin, $attackMax, $img);
        $this->magicAttack = $magicAttack;
    }
    public function MagicAttack($targetObj) {
        if(!mt_rand(0,4)){//５分の１で魔法攻撃
            $attackPoint = $this->magicAttack;
           debug($this->getName().'の魔法攻撃!!');
           History::set($this->getName().'の魔法攻撃!!');
           History::set($targetObj->getName().'は'.$attackPoint.'の大ダメージを受けた!!');
           $targetObj->sayCry();
           $targetObj->setHp($targetObj->getHp() - $attackPoint);
        }else{//それ以外は通常と同じ
            parent::attack();
        }
    }
}
//インスタンス生成 //攻撃力など変更するかも
$human = new Human('勇者',500,30,50,Sex::MAN,mt_rand(100,200));
$monsters[] = new Monster('スライム',40,10,20,'img/img04.png');
$monsters[] = new Monster('スライムクラゲ',50,20,30,'img/img05.png');
$monsters[] = new MagicMonster('悪魔騎士',100,20,50,'img/img01.png',80);
$monsters[] = new MagicMonster ('マスタードラゴン',200,50,80,'img/img02.png',120);
$monsters[] = new MagicMonster ('ひくいドリ',150,30,60,'img/img06.png',100);

//モンスター・人生成関数
function createHuman(){
    global $human;
    $_SESSION['human'] = $human;
}
function createMonster(){
    global $monsters;
    $monster = $monsters[mt_rand(0,4)];
    $_SESSION['monster'] = $monster;
//    History::resetHistory();
    History::set($_SESSION['monster']->getName().'が現れた！');
    if(!empty($_SESSION['knockDownCount']))
        $_SESSION['knockDownCount'] = '';
}
//初期化関数
function init(){
debug('初期化します');
History::clear();
//History::set('初期化します！');
debug('セッション変数の中身'.print_r($_SESSION,true));
$_SESSION['knockDownCount'] = 0;
$_SESSION['jyumon'] = 0;
createHuman();
createMonster();
}
function gameOver(){
    debug('ゲームオーバーになりました！セッションをからにします');
    $_SESSION = array();
//    debug('セッション変数の中身'.print_r($_SESSION,true));
}


//ポスト送信
if(!empty($_POST)){
    debug('ポスト送信されました!');
    debug('セッション変数の中身'.print_r($_SESSION,true));
    debug('ポストの中身'.print_r($_POST,true));
    History::resetHistory();
    $startFlg = (!empty($_POST['start']))  ? true : false;
    $attackFlg = (!empty($_POST['attack']))  ? true : false;
    $jyumonFlg = (!empty($_POST['jyumon'])) ? true : false;
    $escapeFlg = (!empty($_POST['escape'])) ? true : false;
    $surrenderFlg = (!empty($_POST['surrender'])) ? true : false;

    if($startFlg) {
        debug('スタートボタンが押されました!');
        init();
    }else{
        if($attackFlg){
            debug('攻撃ボタンが押されました!');
            debug('モンスターに攻撃します!');
            debug('セッション変数の中身'.print_r($_SESSION,true));
            $_SESSION['human']->attack($_SESSION['monster']);
            $_SESSION['jyumon'] += 1;//jyumonのnoticeエラー
            debug('モンスターから攻撃を受ける');
            $_SESSION['monster']->attack($_SESSION['human']);
            if($_SESSION['human']->getHp() <= 0) {//勇者のHPがゼロ
                debug('勇者のHPがゼロになりました');
                gameOver();
            }else{//モンスターのHPがゼロ　次のモンスター出現
                if($_SESSION['monster']->getHp() <= 0){
                    debug('モンスターを倒した!!');
                    History::set($_SESSION['monster']->getName().'を倒した！');
                    debug('新しいモンスターの出現');
                    createMonster();
                    $_SESSION['knockDownCount'] += 1;
                }
            }
        }elseif($jyumonFlg){
            debug('勇者の呪文攻撃!');
            $_SESSION['human']->jyumon($_SESSION['monster']);
            if($_SESSION['human']->getHp() <= 0) {
                debug('勇者のHPがゼロになりました');
                gameOver();
            }else{//モンスターのHPがゼロ　次のモンスター出現
                if($_SESSION['monster']->getHp() <= 0){//モンスターのHPがゼロ
                    debug('モンスターを倒した!!');
                    History::set($_SESSION['monster']->getName().'を倒した！');
                    debug('新しいモンスターの出現');
                    createMonster();
                    $_SESSION['knockDownCount'] = $_SESSION['knockDownCount'] + 1;
                }
            }

        }elseif($escapeFlg){
            debug('逃げるボタンを押した!!');
            if(!mt_rand(0,4)){//５分の１で回り込まれる
                debug('逃げられない!!');
                History::set('勇者は逃げ出した!!しかしモンスターに回り込まれた！');
                $_SESSION['monster']->attack($_SESSION['human']);
                if($_SESSION['human']->getHp() <= 0){
                    debug('勇者のHPがゼロになりました');
                    gameOver();
                }
            }else{
                debug('うまく逃げだせた！！');
                History::set('逃げた！');
                createMonster();
            }
        }elseif($surrenderFlg){
            debug('降参!!');
            gameOver();
        }

    }
//ポスト空に
    $_POST = array();
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="reset.css">
        <link rel="stylesheet" href="style.css">
        <title>勇者ゲーム</title>
    </head>
    <body>
    <section class="site-width">
        <div>
            <h1 class="site-title">勇者ゲーム</h1>
        </div>
        <?php if(empty($_SESSION)){ ?>
            <div class="form-border">
                <h2 class="start-title">GAME START ?</h2>
                <form method="post">
                    <input type="submit" name="start" value="▶ゲームスタート" class="start-btn">
                </form>
            </div>
            <?php }else{ ?>
        <div class="flex-cntainer">
            <div class="mybox box-style">
                <nav class="list-style">
                    <ul class="ul-item">
                        <li class="item">勇者の残りHP <?php echo $_SESSION['human']->getHp(); ?></li>
                        <li class="item">勇者のMP(5以上で呪文) <?php echo (!empty($_SESSION['jyumon']))? $_SESSION['jyumon']: 0; ?></li>
                        <li class="item">モンスターの残りHP <?php echo $_SESSION['monster']->getHp(); ?></li>
                        <li class="item">倒したモンスターの数 <?php echo (!empty($_SESSION['knockDownCount']))? $_SESSION['knockDownCount']: 0;  ?></li>
                    </ul>
                </nav>
            </div>
            <form method="post" class="form command-style flex-form">
                <span class="triangle  js-hover-action "></span><input type="submit" value="たたかう" name="attack" class="btn btn-style js-btn-action01 js-attack-motion">
                <span class="triangle  js-hover-action "></span><input type="submit" value="じゅもん" name="jyumon" class="btn btn-style js-btn-action02 js-attack-motion">
                <span class="triangle  js-hover-action triangle-style03"></span><input type="submit" value="にげる"   name="escape"  class="btn btn-style js-btn-action03">
                <span class="triangle  js-hover-action "></span><input type="submit" value="降参する" name="surrender"   class="btn  btn-style js-btn-action04">
            </form>
        </div>
        <div class="monster-img">
            <img src="<?php echo $_SESSION['monster']->getImg(); ?>" alt="" class="monster-img-style js-img-motion">
        </div>
        <div class="column-style">
             <p><?php echo (!empty($_SESSION['history']))? $_SESSION['history']: ''; ?></p>
        </div>
        <?php } ?>
    </section>
    <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous"></script>
    <script src="app.js"></script>
    </body>
</html>