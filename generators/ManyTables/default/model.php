<?php

use yii\helpers\Inflector;
/**
 * This is the template for generating the model class of a specified table.
 */


echo "<?php\n";
?>

namespace app\models;

use Yii;

class <?= $modelName ?> extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?=$tableName?>';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . "\n        " ?>];
    }

    /**
    * @param string $value of <?=$primaryKey?>.
    * @return ActiveRecord|null ActiveRecord instance matching the condition, or null if nothing matches.
    */
    public function findBy<?=ucfirst($primaryKey)?>($value)
    {
        return self::findOne(['<?=$primaryKey?>' => $value]);
    }


    <?php foreach (explode(',',$fields) as $fieldName): ?>

    /**
    * @param string value of <?=$fieldName?>.
    * @return ActiveRecord ActiveRecord instance with setted attribute.
    */
    public function <?=Inflector::variablize("set".ucfirst($fieldName))?>($value)
    {
        $this->attributes['<?=$fieldName?>'] = $value;
        return $this;
    }

    /**
    * @return <?=$fieldName?> attribute value.
    */
    public function <?=Inflector::variablize("get".ucfirst($fieldName))?>()
    {
        return $this->attributes['<?=$fieldName?>'];
    }

    <?php endforeach;?>
}
