<?php

class Boostrap
{
	private string $wpVersion = '5.8.1';
	private ?string $phpUnitVersion = null;

	private string $pathRoot = '';
	private string $pathConfig = '';
	private string $pathWordPress = '';

	private string $dbHost = 'mysql:3306';
	private string $dbName = 'wp_auth0_test';
	private string $dbUser = 'wp_auth0_test';
	private string $dbPass = 'wp_auth0_test';

	private string $regexConfigSearch = '/^define\((?:.*)\'%KEY%\',(.*)\);$/m';
	private string $regexConfigReplace = 'define( \'%KEY%\', \'%VALUE%\' );';

	public function __construct(?string $wpVersion = null, ?string $phpUnitVersion = null, ?string $pathRoot = null, ?string $pathConfig = null, ?string $pathWordPress = null, ?string $dbHost = null, ?string $dbName = null, ?string $dbUser = null, ?string $dbPass = null)
	{
		$this->wpVersion = $wpVersion ?? $this->wpVersion;
		$this->phpUnitVersion = $phpUnitVersion ?? $this->phpUnitVersion;

		$this->pathRoot = $pathRoot ?? realpath(join(DIRECTORY_SEPARATOR, [realpath(__DIR__), '..']));
		$this->pathConfig = $pathConfig ?? join(DIRECTORY_SEPARATOR, [$this->pathRoot, 'tests-wordpress-config']);
		$this->pathWordPress = $pathWordPress ?? join(DIRECTORY_SEPARATOR, [$this->pathRoot, 'tests-wordpress', $this->wpVersion]);

		$this->dbHost = $dbHost ?? $this->dbHost;
		$this->dbName = $dbName ?? $this->dbName;
		$this->dbUser = $dbUser ?? $this->dbUser;
		$this->dbPass = $dbPass ?? $this->dbPass;

		if ($this->phpUnitVersion === null) {
			$this->phpUnitVersion = '9.0';

			if (version_compare($this->wpVersion, '5.9.0', '<')) {
				$this->phpUnitVersion = '7.0';
			}
		}
	}

	public function run()
	{
		$this->setup();

		define('WP_TESTS_CONFIG_FILE_PATH', join(DIRECTORY_SEPARATOR, [$this->pathConfig, 'wp-tests-config.php']));

		chdir($this->pathRoot);

		require_once join(DIRECTORY_SEPARATOR, [$this->pathWordPress, 'tests', 'phpunit', 'includes', 'functions.php']);

		tests_add_filter('muplugins_loaded', [$this, 'invokeHook']);

		$this->setupDatabase();

		require_once join(DIRECTORY_SEPARATOR, [$this->pathWordPress, 'tests', 'phpunit', 'includes', 'bootstrap.php']);

		require_once join(DIRECTORY_SEPARATOR, [$this->pathRoot, 'vendor', 'autoload.php']);
	}

	public function setup()
	{
		$this->setupPaths();
		$this->setupWordPress();
		$this->setupConfiguration();
	}

	public function setupPaths()
	{
		if ( ! file_exists($this->pathConfig)) {
			mkdir($this->pathConfig, 0755, true);
		}

		if ( ! file_exists($this->pathWordPress)) {
			mkdir($this->pathWordPress, 0755, true);
		}
	}

	public function setupWordPress()
	{
		chdir($this->pathWordPress);

		if ( ! file_exists(join(DIRECTORY_SEPARATOR, [$this->pathWordPress, 'composer.lock']))) {
			exec('git clone https://github.com/WordPress/WordPress-develop . --branch ' . $this->wpVersion . ' --single-branch');
			$this->setupPatches();
		}
	}

	public function setupPatches()
	{
		system('git apply ' . join(DIRECTORY_SEPARATOR, [$this->pathRoot, 'tests', 'diffs', '50482.diff']));
	}

	public function setupConfiguration()
	{
		copy(join(DIRECTORY_SEPARATOR, [$this->pathWordPress, 'wp-tests-config-sample.php']), join(DIRECTORY_SEPARATOR, [$this->pathConfig, 'wp-tests-config.php']));

		if ( file_exists(join(DIRECTORY_SEPARATOR, [$this->pathConfig, 'wp-tests-config.php']))) {
			unlink(join(DIRECTORY_SEPARATOR, [$this->pathConfig, 'wp-tests-config.php']));
		}

		copy(join(DIRECTORY_SEPARATOR, [$this->pathWordPress, 'wp-tests-config-sample.php']), join(DIRECTORY_SEPARATOR, [$this->pathConfig, 'wp-tests-config.php']));

		$configuration = file_get_contents(join(DIRECTORY_SEPARATOR, [$this->pathConfig, 'wp-tests-config.php']));
		$configuration = $this->mutateConfiguration($configuration, 'ABSPATH', join(DIRECTORY_SEPARATOR, [$this->pathRoot, 'tests-wordpress', $this->wpVersion, 'src']) . DIRECTORY_SEPARATOR);
		$configuration = $this->mutateConfiguration($configuration, 'DB_HOST', $this->dbHost);
		$configuration = $this->mutateConfiguration($configuration, 'DB_NAME', $this->dbName);
		$configuration = $this->mutateConfiguration($configuration, 'DB_USER', $this->dbUser);
		$configuration = $this->mutateConfiguration($configuration, 'DB_PASSWORD', $this->dbPass);
		file_put_contents(join(DIRECTORY_SEPARATOR, [$this->pathConfig, 'wp-tests-config.php']), $configuration);
	}

	public function setupDatabase()
	{
		$connected = false;
		$waited = 0;
		$delay = 3;

		$mysql = mysqli_init();

		while($connected === false) {
			echo "Waiting for MySQL server availability ... ";

			@mysqli_real_connect($mysql, $this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);

			if (! $mysql->connect_error) {
				$connected = true;
				echo "SUCCESS" . PHP_EOL;
				break;
			}

			$waited = $waited + $delay;

			if ($waited >= 30) {
				echo "FAILED" . PHP_EOL;
				exit;
			}

			echo "HOLDING" . PHP_EOL;
			sleep($delay);
		}
	}

	public function invokeHook()
	{
		require_once join(DIRECTORY_SEPARATOR, [$this->pathRoot, 'WP_Auth0.php']);
	}

	private function mutateConfiguration(string $configuration, string $key, string $value)
	{
		return preg_replace(str_replace('%KEY%', $key, $this->regexConfigSearch), str_replace(['%KEY%', '%VALUE%'], [$key, $value], $this->regexConfigReplace), $configuration, 1);
	}
}

$bootstrap = (new Boostrap())->run();
