<?php
/**
 *
 * @author Charles R. Portwood II <charlesportwoodii@ethreal.net>
 * @package CiiMS https://www.github.com/charlesportwoodii/CiiMS
 * @license MIT License
 * @copyright 2011-2014 Charles R. Portwood II
 *
 * @notice  This file is part of CiiMS, and likely will not function without the necessary CiiMS classes
 */
class DefaultController extends CController
{
    /**
     * @var string $layout
     * The layout we want to use for the installer
     */
    public $layout = 'main';
    
    /**
     * Error Action
     * The installer shouldn't error, if this happens, flat out die and blame the developer
     */
    public function actionError()
    {
        $error = array();
        if (!empty(Yii::app()->errorHandler->error))
            $error=Yii::app()->errorHandler->error;
        
        $this->render('error', array('error' => $error));
    }
    
    /**
     * Initial action the user arrives to.
     * Handles setting up the database connection
     */
    public function actionIndex()
    {
        $model = new DatabaseForm;
        
        // Assign previously set credentials
        if (Cii::get(Yii::app()->session['dsn']) != "")
            $model->attributes = Yii::app()->session['dsn'];
        
        // If a post request was sent
        if (Cii::get($_POST, 'DatabaseForm'))
        {
            $model->attributes = $_POST['DatabaseForm'];
            
            if ($model->validateConnection())
            {
                Yii::app()->session['dsn'] = $model->attributes;
                $this->redirect($this->createUrl('/migrate'));
            }
            else
            {
                Yii::app()->user->setFlash('error', Yii::t('Install.main', '{{warning}} {{error}}', array(
                    '{{warning}}' => CHtml::tag('strong', array(), Yii::t('Install.main', 'Warning!')),
                    '{{error}}' => $model->getError('dsn')
                )));
            }
        }

        $this->render('index', array('model'=>$model));
    }
    
    /**
     * Handles the database migrations
     * This is the whole point/benefit of wrapping the installer in Yii. We can run CDbMigrations
     * directly from the web app itself, which means the installer is _must_ cleaner
     */
    public function actionMigrate()
    {
        ignore_user_abort(true);
        set_time_limit(0);
        
        // Don't let the user get to this action if they haven't setup a DSN yet.
        if (Yii::app()->session['dsn'] == "")
            $this->redirect($this->createUrl('/'));

        $this->render('migrate');
    }
    
    /**
     * This action enables us to create an admin user for CiiMS
     */
    public function actionCreateAdmin()
    {        
        $model = new UserForm;
        
        if (Cii::get($_POST, 'UserForm') != NULL)
        {
            $model->attributes = Cii::get($_POST, 'UserForm', array());
            if ($model->validate())
            {
                $response = $this->runInstaller($model);
                if (file_exists(Yii::getPathOfAlias('application.config').DS.'/main.php'))
                {
                    $this->render('admin');
                    Yii::app()->end();
                }
                else
                    Yii::app()->user->setFlash('error', $response);
            }
            
            $errors = $model->getErrors();
            $firstError = array_values($errors);
            Yii::app()->user->setFlash('error',  Yii::t('Install.main', '{{warning}} {{error}}', array(
                '{{warning}}' => CHtml::tag('strong', array(), Yii::t('Install.main', 'Warning!')),
                '{{error}}' => $firstError[0][0]
            )));
        }
        
        $this->render('createadmin', array('model' => $model));
    }
    
    /**
     * Ajax comment to run CDbMigrations
     */
    public function actionRunMigrations()
    {
        header('Content-Type: application/json');

        $response = $this->runMigrationTool();
        
        $data = array('migrated' => false, 'details' => $response);
        
        if (strpos($response, 'Migrated up successfully.') || strpos($response, 'Your system is up-to-date.'))
            $data = array('migrated' => true, 'details' => $response);
        
        echo CJavaScript::jsonEncode($data);
        Yii::app()->end();
    }

    /**
     * Runs the migration tool CLI Command
     * @param array $dsn
     * @return string
     */
    private function runMigrationTool()
    {
        return $this->runCommand(Yii::app()->session['dsn'], 'application.commands.CiiMigrateCommand', array(
            'yiic',
            'migrate'
        ));
    }


    /**
     * Runs the CLI installer which writes the config files and create the initial admin user
     * @param $userModel UserForm
     * @return string
     */
    private function runInstaller($userModel)
    {
        return $this->runCommand(Yii::app()->session['dsn'], 'application.modules.install.InstallerCommand', array(
            'yiic',
            'installer',
            'index',
            '--dbHost='.Yii::app()->session['dsn']['host'],
            '--dbName='.Yii::app()->session['dsn']['dbname'],
            '--dbUsername='.Yii::app()->session['dsn']['username'],
            '--dbPassword='.Yii::app()->session['dsn']['password'],
            '--adminEmail='.$userModel->email,
            '--adminPassword='.$userModel->password,
            '--adminUsername='.$userModel->username,
            '--siteName='.$userModel->siteName,
            '--force=true'
        ));
    }

    /**
     * Runs a given command
     * @param array $dsn        The DSN
     * @param string $command   The CLI command to run
     * @param array $data       The runner data to use
     * @return string
     */
    private function runCommand($dsn, $command, $data)
    {
        $runner=new CConsoleCommandRunner();
        $runner->commands=array(
            'migrate' => array(
                'class' => $command,
                'dsn' => $dsn,
                'interactive' => 0,
            ),
            'db'=>array(
                'class'=>'CDbConnection',
                'connectionString' => "mysql:host={$dsn['host']};dbname={$dsn['dbname']}",
                'emulatePrepare' => true,
                'username' => $dsn['username'],
                'password' => $dsn['password'],
                'charset' => 'utf8',
            ),
        );
        
        ob_start();
        $modules = array_filter(glob(Yii::app()->getBasePath().'/modules/*', GLOB_ONLYDIR));

        foreach ($modules as $module)
        {
            if (file_exists($module.'/commands'))
                $runner->addCommands($module.'/commands');
        }

        $runner->run($data);
        
        return htmlentities(ob_get_clean(), null, Yii::app()->charset);
    }
}
