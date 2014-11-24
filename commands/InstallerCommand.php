<?php

Yii::import('ext.cii.commands.CiiConsoleCommand');
Yii::import('application.modules.install.models.*');
class InstallerCommand extends CiiConsoleCommand
{
	public function actionIndex($dbHost, $dbName, $dbUsername, $dbPassword, $adminEmail, $adminPassword, $adminUsername, $siteName, $force=false)
	{
		if ($force===false && file_exists(Yii::getPathOfAlias('application.config').'/main.php'))
			$this->log(Yii::t('Install.main', 'The installer cannot run, because CiiMS is already installed'), true);

		$databaseForm = new DatabaseForm;
		$databaseForm->attributes = array(
			'username' => $dbUsername,
			'password' => $dbPassword,
			'host'     => $dbHost,
			'dbname'   => $dbName
		);

		// Verify there is a valid connection
		if (!$databaseForm->validateConnection())
			$this->log($databaseForm->getError('dsn'));

		// Run the migration
		$migration = $this->runMigrationTool($databaseForm->attributes);

		// Verify the migration completed
		if (!strpos($migration, 'Migrated up successfully.') && !strpos($migration, 'Your system is up-to-date.'))
			$this->log(Yii::t('Install.main', 'Migrations failed to complete. Please correct any errors, and run `php protected/yiic.php migrate up` manually to verify the database is installed.'), true);
		
		$userForm = new UserForm;
		$userForm->attributes = array(
			'email'    => $adminEmail,
			'password' => $adminPassword,
			'username' => $adminUsername,
			'siteName' => $siteName
		);

		// Validate that the user information is valid
		if (!$userForm->validateForm())
			$this->log(print_r($userForm->getErrors(), true));

		// Write the user to the database
		try {
			$connection = $databaseForm->getConnection();
	        $connection->createCommand('INSERT INTO users (id, email, password, username, user_role, status, created, updated) VALUES (1, :email, :password, :username, 9, 1, UNIX_TIMESTAMP(),UNIX_TIMESTAMP())')
	                   ->bindParam(':email',        $userForm->email)
	                   ->bindParam(':password',     $userForm->encryptedPassword)
	                   ->bindParam(':username',     $userForm->username)
	                   ->execute();
	    } catch (Exception $e) {
	    	$this->log(Yii::t('Install.main', 'CiiMS was unable to add your user to the database.'), true);
	    }

		// Write the config files to disk
		$this->generateConfigFile($databaseForm->attributes, $siteName, $userForm->encryptionKey);

		$this->log(Yii::t('Install.main', 'CiiMS has successfully been installed!'), true);
	}

	/**
     * Runs the migration tool, effectivly installing the database an all appliciable default settings
     */
    private function runMigrationTool(array $dsn)
    {
        $runner=new CConsoleCommandRunner();
        $runner->commands=array(
            'migrate' => array(
                'class' => 'application.commands.CiiMigrateCommand',
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
        $runner->run(array(
            'yiic',
            'migrate'
        ));
        
        return htmlentities(ob_get_clean(), null, Yii::app()->charset);
    }

        /**
     * Generates a configuration file inside our config directory
     * Writes to /config/main.php
     */
    private function generateConfigFile($db, $siteName, $key)
    {
        $user = $db['username'];
        $pass = $db['password'];
        $dsn  = $db['dsn'];

        $config = "<?php return array(
	        'name' => '{$siteName}',
	        'components' => array(
	            'db' => array(
	                'class' => 'CDbConnection',
	                'connectionString' => '{$dsn}',
	                'emulatePrepare' => true,
	                'username' => '{$user}',
	                'password' => '{$pass}',
	                'charset' => 'utf8',
	                'schemaCachingDuration' => '3600',
	                'enableProfiling' => true,
	            ),
	            'cache' => array(
	                'class' => 'CFileCache',
	            ),
	        ),
	        'params' => require('params.php')
	    );";
        
        $params = "<?php return array(
        	'encryptionKey' => '{$key}'
        );";

        // Write the configuration file out
        $this->writeFile(Yii::getPathOfAlias('application.config').DS.'main.php', $config);
        $this->writeFile(Yii::getPathOfAlias('application.config').DS.'params.php', $params);
    }

    /**
     * Writes data to a file
     * @param  string $filePath The path alias
     * @param  mixed  $data     The data we want to write
     */
    private function writeFile($filePath, $data)
    {
        $fh = fopen($filePath, 'w+');
        fwrite($fh, $data);
        fclose($fh);
    }
}