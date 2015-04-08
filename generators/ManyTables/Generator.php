<?php

namespace DHTMLX\Gii\ManyTables;

use Yii;
use yii\gii\CodeFile;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\helpers\Inflector;
use yii\helpers\Url;


class Generator extends \yii\gii\Generator
{


    /**
     * @var string the table name
     */
    public $tableNames;
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

    protected $lastControllerName;
    protected $classNames;
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'DHTMLX Many Tables Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator helps you to quickly generate many editable tables based on DHTMLX library';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['db'], 'filter', 'filter' => 'trim'],
            [['db'], 'required']

            //['controllerName', 'modelName', 'actionName' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
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
            'layout.php'
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
            'db' => 'This is the ID of the DB application component.',
        ];
    }

    /**
     * @inheritdoc
     */
    public function successMessage()
    {

        $url = strtolower($this->lastControllerName)."/table";
        return "<a href=".Url::to('@web/'.$url, true).">Click</a> to go to created page";
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $db = $this->getDbConnection();

        $files = [];
        $tables = [];
        $this->getTableNames();

        $counter = 0;

        foreach ($this->tableNames as $table) {
            $tables[$counter]['url'] = strtolower(Inflector::classify($table))."/table";
            $tables[$counter]['comma'] = count($this->tableNames) == count($tables) ? '' : ',';
            $tables[$counter++]['name'] = $table;
        }

        foreach ($this->tableNames as $table) {

            $tableSchema = $db->getTableSchema($table);
            $primaryKey = $tableSchema->primaryKey[0];
            $fieldsArray  = [];
            $headersArray = [];

            foreach ($tableSchema->columns as $column) {
                if (!$column->autoIncrement) {
                    array_push($fieldsArray, $column->name);
                    array_push($headersArray, Inflector::humanize($column->name));
                }
            }

            $fields  = implode(',', $fieldsArray);
            $headers = implode(',', $headersArray);

            $modelControllerName = ucfirst(strtolower(Inflector::classify($table)));



            $files[] = new CodeFile(
                $this->getModelFile($modelControllerName),
                $this->render('model.php',['fields'    => $fields,
                                           'rules'     => $this->generateRules($tableSchema),
                                           'modelName' => $modelControllerName,
                                           'primaryKey'=> $primaryKey,
                                           'tableName' => $table])
            );

            $files[] = new CodeFile(
                $this->getControllerFile($modelControllerName),
                $this->render('controller.php',['fields'         => $fields,
                                                'controllerName' => $modelControllerName,
                                                'modelName'      => $modelControllerName,
                                                'primaryKey'     => $primaryKey])
            );

            $files[] = new CodeFile(
                $this->getViewFile($modelControllerName),
                $this->render('view.php',['headers'    => $headers,
                                          'tableName' => $table,
                                          'tables'    => $tables,
                                          'controllerName' => strtolower($modelControllerName)])
            );



        }

        $files[] = new CodeFile(
            Yii::getAlias($this->viewPath) . '/layouts/fullscreen.php',
            $this->render('layout.php')
        );

        $this->lastControllerName = $modelControllerName;

        return $files;
    }


    /**
     * @return string the controller class file path
     */
    public function getControllerFile($controllerName)
    {
        return Yii::getAlias($this->controllerPath) . '/' . ucfirst(strtolower($controllerName)) . 'Controller.php';
    }

    /**
     * @return string the controller class file path
     */
    public function getModelFile($modelName)
    {
        return Yii::getAlias($this->modelPath) . '/' . $modelName . '.php';
    }

    public function getViewFile($controllerName)
    {
        return Yii::getAlias($this->viewPath) . '/' .strtolower($controllerName).'/table.php';
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

        $this->tableNames = $db->schema->getTableNames();

        return $this->tableNames;
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
