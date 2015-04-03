<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace DHTMLX\Gii\SingleTable;

use Yii;
use yii\gii\CodeFile;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This generator will generate a controller and one or a few action view files.
 *
 * @property array $actionIDs An array of action IDs entered by the user. This property is read-only.
 * @property string $controllerFile The controller class file path. This property is read-only.
 * @property string $controllerID The controller ID. This property is read-only.
 * @property string $controllerNamespace The namespace of the controller class. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    /**
     * @var string the controller class name
     */
    public $controllerName;
    /**
     * @var string the model class name
     */
    public $modelName;

    /**
     * @var string the primary key
     */
    public $primaryKey = 'id';
    /**
     * @var string the table name
     */
    public $tableName;
    /**
     * @var string the action name
     */
    public $actionName;
    /**
     * @var string list of fields in table separated by commas or spaces
     */
    public $fieldsName = '';

    public $viewPath = '@app/views';
    public $controllerPath = '@app/controllers';
    public $modelPath = '@app/models';

    public $db = 'db';
    protected $tableNames;
    protected $classNames;
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'DHTMLX Single Table Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator helps you to quickly generate an editable table based on DHTMLX library';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controllerName',  'modelName', 'actionName', 'tableName','primaryKey'], 'filter', 'filter' => 'trim'],
            [['controllerName', 'modelName', 'actionName', 'tableName', 'primaryKey'], 'required'],
            [['controllerName'], 'validateController'],

            //['controllerName', 'modelName', 'actionName' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.']
        ]);
    }

    /**
     * Validates [[controllerName]] to make sure it is a valid controller and doesn't include 'controller' word.
     */
    public function validateController()
    {
        if (stripos($this->controllerName, 'controller')) {
            $this->addError('controllerName', "Please, remove the word 'controller' - it will be added automatically");
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'controllerName' => 'Controller Name',
            'modelName' => 'Model Name',
            'actionName' => 'Action Name',
            'tableName' => 'Table Name',
            'db' => 'Database Connection ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return [
            'controller.php',
            'view.php',
            'model.php',
        ];
    }

    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();

        if ($db !== null) {
            return [
                'tableName' => function () use ($db) {
                    return $db->getSchema()->getTableNames();
                },
            ];
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return [
            'controllerName' => 'This is the name of the controller class to be generated. ,
                and class name should be in CamelCase. Ending <code>Controller</code> will be added automatically.',
            'actions' => 'Action name that will be the last argument of url',
            'modelName' => 'Model name. First  character will be made capital automatically',
            'db' => 'This is the ID of the DB application component.',
        ];
    }

    /**
     * @inheritdoc
     */
    public function successMessage()
    {
        $url = "/".strtolower($this->controllerName)."/".strtolower($this->actionName);
        return "<a href=".Url::toRoute($url).">Click</a> to go to created page";
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $db = $this->getDbConnection();
        $tableSchema = $db->getTableSchema($this->tableName);
        $fieldsArray = [];
        foreach ($tableSchema->columns as $column) {
            if (!$column->autoIncrement)
                array_push($fieldsArray,$column->name);
        }

        $this->fieldsName = implode(',', $fieldsArray);

        $files = [];

        $files[] = new CodeFile(
            $this->getModelFile(),
            $this->render('model.php',['fields' => $this->fieldsName,
                                       'rules' => $this->generateRules($tableSchema),
                                       'modelName' => $this->modelName,
                                       'tableName' => $this->tableName,
                                       'primaryKey' => $this->primaryKey
                                      ])
        );

        $files[] = new CodeFile(
            $this->getControllerFile(),
            $this->render('controller.php')
        );

        $files[] = new CodeFile(
            $this->getViewFile(),
            $this->render('view.php',['fields' => $this->fieldsName])
        );


        return $files;
    }


    /**
     * @return string the controller class file path
     */
    public function getControllerFile()
    {
        return Yii::getAlias($this->controllerPath) . '/' . ucfirst($this->controllerName) . 'Controller.php';
    }

    /**
     * @return string the controller class file path
     */
    public function getModelFile()
    {
        return Yii::getAlias($this->modelPath) . '/' . $this->modelName . '.php';
    }

    public function getViewFile()
    {
        return Yii::getAlias($this->viewPath) . '/' .strtolower($this->controllerName).'/' .$this->actionName . '.php';
    }

    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    protected function getTableNames()
    {
        if ($this->tableNames !== null) {
            return $this->tableNames;
        }
        $db = $this->getDbConnection();
        if ($db === null) {
            return [];
        }
        $tableNames = [];
        if (strpos($this->tableName, '*') !== false) {
            if (($pos = strrpos($this->tableName, '.')) !== false) {
                $schema = substr($this->tableName, 0, $pos);
                $pattern = '/^' . str_replace('*', '\w+', substr($this->tableName, $pos + 1)) . '$/';
            } else {
                $schema = '';
                $pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';
            }

            foreach ($db->schema->getTableNames($schema) as $table) {
                if (preg_match($pattern, $table)) {
                    $tableNames[] = $schema === '' ? $table : ($schema . '.' . $table);
                }
            }
        } elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
            $tableNames[] = $this->tableName;
            $this->classNames[$this->tableName] = $this->modelName;
        }

        return $this->tableNames = $tableNames;
    }

    /**
     * Generates a class name from the specified table name.
     * @param string $tableName the table name (which may contain schema prefix)
     * @return string the generated class name
     */
    protected function generateClassName($tableName)
    {
        if (isset($this->classNames[$tableName])) {
            return $this->classNames[$tableName];
        }

        if (($pos = strrpos($tableName, '.')) !== false) {
            $tableName = substr($tableName, $pos + 1);
        }

        $db = $this->getDbConnection();
        $patterns = [];
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";
        if (strpos($this->tableName, '*') !== false) {
            $pattern = $this->tableName;
            if (($pos = strrpos($pattern, '.')) !== false) {
                $pattern = substr($pattern, $pos + 1);
            }
            $patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
        }
        $className = $tableName;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $tableName, $matches)) {
                $className = $matches[1];
                break;
            }
        }

        return $this->classNames[$tableName] = Inflector::id2camel($className, '_');
    }

    /**
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->get($this->db, true);
    }

    /**
     * Generates validation rules for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated validation rules
     */
    public function generateRules($table)
    {
        $types = [];
        $lengths = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if (!$column->allowNull && $column->defaultValue === null) {
                $types['required'][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
            }
        }
        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        // Unique indexes rules
        try {
            $db = $this->getDbConnection();
            $uniqueIndexes = $db->getSchema()->findUniqueIndexes($table);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount == 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $labels = array_intersect_key($this->generateLabels($table), array_flip($uniqueColumns));
                        $lastLabel = array_pop($labels);
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['" . $columnsList . "'], 'unique', 'targetAttribute' => ['" . $columnsList . "'], 'message' => 'The combination of " . implode(', ', $labels) . " and " . $lastLabel . " has already been taken.']";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        return $rules;
    }

    /**
     * Checks if any of the specified columns is auto incremental.
     * @param \yii\db\TableSchema $table the table schema
     * @param array $columns columns to check for autoIncrement property
     * @return boolean whether any of the specified columns is auto incremental.
     */
    protected function isColumnAutoIncremental($table, $columns)
    {
        foreach ($columns as $column) {
            if (isset($table->columns[$column]) && $table->columns[$column]->autoIncrement) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates the attribute labels for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated attribute labels (name => label)
     */
    public function generateLabels($table)
    {
        $labels = [];
        foreach ($table->columns as $column) {
            if (!strcasecmp($column->name, 'id')) {
                $labels[$column->name] = 'ID';
            } else {
                $label = Inflector::camel2words($column->name);
                if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                    $label = substr($label, 0, -3) . ' ID';
                }
                $labels[$column->name] = $label;
            }
        }

        return $labels;
    }


}
