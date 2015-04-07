<?php
echo "<?php\n";
?>
use yii\helpers\Html;
use app\assets\AppAsset;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

/* @var $this \yii\web\View */
/* @var $content string */


AppAsset::register($this);
?>
<?="<?php";?> $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?="<?=";?>  Yii::$app->language ?>">
<head>
    <meta charset="<?="<?=";?> Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?="<?=";?> Html::csrfMetaTags() ?>
    <title><?="<?=";?> Html::encode($this->title) ?></title>
    <?="<?=";?> $this->head() ?>

</head>
<body style="overflow: hidden;">
<?="<?php";?> $this->beginBody() ?>

<div class="wrap">
    <?="<?php";?>
    NavBar::begin([
    'brandLabel' => 'My Company',
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
    'class' => 'navbar-inverse navbar-fixed-top',
    ],
    ]);
    echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
    ['label' => 'Home', 'url' => ['/site/index']],
    ['label' => 'About', 'url' => ['/site/about']],
    ['label' => 'Contact', 'url' => ['/site/contact']],
    Yii::$app->user->isGuest ?
    ['label' => 'Login', 'url' => ['/site/login']] :
    ['label' => 'Logout (' . Yii::$app->user->identity->username . ')',
    'url' => ['/site/logout'],
    'linkOptions' => ['data-method' => 'post']],
    ],
    ]);
    NavBar::end();
    ?>
    <?="<?=";?> $content ?>
</div>

<?="<?php";?> $this->endBody() ?>
</body>
</html>
<?="<?php";?> $this->endPage() ?>
?>