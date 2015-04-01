<?php
echo "<?php\n";
/**
 * This is the template for generating a controller class file.
 */

echo "namespace app\\controllers;";
?>

<?= 'use app\models\\'.$generator->modelName ?>;

use DHTMLX\Connector\GridConnector;
use Yii;
use yii\web\Controller;


class <?= $generator->controllerName ?>Controller extends Controller
{

    public $enableCsrfValidation = false;

    public function action<?= ucfirst($generator->actionName) ?>()
    {
        return $this->render('<?= $generator->actionName ?>');
    }

    public function action<?= ucfirst($generator->actionName) ?>_data()
    {

        $model = new <?=$generator->modelName?>();
        $connector = new GridConnector($model,"PHPYii2");
        $connector->configure($model->tableName(), "<?=$generator->primaryKey?>", "<?=$generator->fieldsName?>");
        $connector->render();

    }


}
