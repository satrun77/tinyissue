<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use League\Flysystem\Adapter\Local as Adapter;
use League\Flysystem\Filesystem;
use Tinyissue\Model;

/**
 * Install is console command to install the Tiny Issue application
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Install extends Command
{
    const COLOR_GOOD = 'green';
    const COLOR_BAD = 'red';
    const COLOR_INFO = 'blue';
    const EMPTY_VALUE = 'empty value';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tinyissue:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Tinyissue.';

    /**
     * Required PHP modules
     *
     * @var array
     */
    protected $modules = [
        'pdo' => 0,
        'mcrypt' => 0,
        'openssl' => 0,
        'curl' => 0,
        'json' => 0,
        'mbstring' => 0,
        'xml' => 0,
    ];

    /**
     * Minimum PHP version
     *
     * @var string
     */
    protected $phpVersion = '5.5.0';

    /**
     * Supported drivers
     *
     * @var array
     */
    protected $dbDrivers = [
        'pdo_sqlite' => 0,
        'pdo_mysql' => 0,
        'pdo_pgsql' => 0,
        'pdo_sqlsrv' => 0,
    ];

    /**
     * Current user entered data & default values
     *
     * @var array
     */
    protected $data = [
        'key' => '',
        'timezone' => 'Pacific/Auckland',
        'dbHost' => 'localhost',
        'dbName' => 'tinyissue',
        'dbUser' => 'root',
        'dbPass' => self::EMPTY_VALUE,
        'dbDriver' => 'mysql',
        'dbPrefix' => '',
        'sysEmail' => '',
        'sysName' => '',
        'adminEmail' => '',
        'adminFirstName' => '',
        'adminLastName' => '',
        'adminPass' => '',
    ];

    /**
     * The status of the environment check
     *
     * @var bool
     */
    protected $envStatus = true;

    /**
     * Environment requirements for display status table
     *
     * @var array
     */
    protected $envRequirements = [];

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function fire()
    {
        if (!$this->checkEnvironment()) {
            return false;
        }

        Artisan::call('down');
        $this->loop('stageOne');
        $this->loop('stageTwo');
        Artisan::call('up');

        $this->line('<fg=green>Instalation complete.</fg=green>');

        return true;
    }

    /**
     * Check the current environment and display the result in table
     *
     * @return bool
     */
    protected function checkEnvironment()
    {
        // Check PHP modules
        $this->checkPhpExtension($this->modules, '{module} extension', true, 'No');

        // Check db drivers
        $this->checkPhpExtension($this->dbDrivers, '{module} driver for pdo', false, 'Not Found');

        // Whether or not one or more valid drivers were found
        $validDrivers = $this->getValidDbDrivers();
        $dbDriverStatus = !empty($validDrivers);
        if (!$dbDriverStatus) {
            $dbDriverTitle = 'Install one of the following pdo drivers ('
                . implode(', ', array_keys($this->dbDrivers)) . ')';
        } else {
            $dbDriverTitle = 'You can choose one of the following pdo drivers ('
                . implode(', ', $validDrivers) . ')';
        }
        $this->envRequirementsRow($dbDriverTitle, $dbDriverStatus, true, 'No Found');

        // Check PHP version
        $phpVersionStatus = version_compare(PHP_VERSION, $this->phpVersion, '>=');
        $this->envRequirementsRow('PHP version ' . $this->phpVersion . ' or above is needed.', $phpVersionStatus, true);

        // Check application upload directory
        $this->envRequirementsRow('Upload directory is writable.', is_writable(base_path('storage/app/uploads')));

        // Check if upload directory is accessible to the public
        $url = $this->isUploadDirectoryPublic();
        if (!empty($url)) {
            $this->envRequirementsRow('Upload directory maybe accessible from (' . $url . ').');
        }

        // Display the result table
        $this->table(['Requirement', 'Status'], $this->envRequirements);

        return $this->envStatus;
    }

    /**
     * Check the availability of list of php extensions
     *
     * @param array  $modules
     * @param string $labelFormat
     * @param bool   $required
     * @param string $failedLabel
     *
     * @return $this
     */
    protected function checkPhpExtension(array &$modules, $labelFormat, $required = true, $failedLabel = 'No')
    {
        foreach ($modules as $module => $status) {
            $title = str_replace(['{module}'], [$module], $labelFormat);
            $status = extension_loaded($module);
            $modules[$module] = $status;
            $this->envRequirementsRow($title, $status, $required, $failedLabel);
        }

        return $this;
    }

    /**
     * Render environment requirement row
     *
     * @param string $label
     * @param bool   $status
     * @param bool   $required
     * @param string $failedLabel
     *
     * @return $this
     */
    protected function envRequirementsRow($label, $status = false, $required = false, $failedLabel = 'No')
    {
        $statusColor = $status ? static::COLOR_GOOD : ($required ? static::COLOR_BAD : static::COLOR_INFO);
        $statusTitle = $status ? 'OK' : $failedLabel;
        $this->envRequirements[] = $this->formatTableCells([$label, $statusTitle], $statusColor);
        if ($required) {
            $this->envStatus = $status;
        }

        return $this;
    }

    /**
     * Format cell text color
     *
     * @param string[] $cells
     * @param string   $color
     *
     * @return array
     */
    protected function formatTableCells(array $cells, $color)
    {
        return array_map(function ($cell) use ($color) {
            return '<fg=' . $color . '>' . $cell . '</fg=' . $color . '>';
        }, $cells);
    }

    /**
     * Returns list of valid db drivers
     *
     * @return array
     */
    protected function getValidDbDrivers()
    {
        return array_keys(array_filter($this->dbDrivers, function ($item) {
            return $item === true;
        }));
    }

    /**
     * Check if upload directory is accessible to the public
     *
     * @return string
     */
    protected function isUploadDirectoryPublic()
    {
        $pathSegments = explode('/', base_path());
        $count = count($pathSegments);
        $indexes = [];
        for ($i = 0; $i < $count; ++$i) {
            $indexes[] = $i;
            $path = implode('/', array_except($pathSegments, $indexes));
            $guessUrl = url($path . '/storage/app/uploads');
            $curl = curl_init($guessUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code != '404') {
                return $guessUrl;
            }
        }

        return '';
    }

    /**
     * Start a stage loop
     *
     * @param string $method The method name to execute in a loop
     *
     * @return void
     */
    protected function loop($method)
    {
        while (true) {
            try {
                $this->$method();
                break;
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                $this->comment($e->getTraceAsString());
                $this->line('... Start again');
                $this->line('');
            }
        }
    }

    /**
     * Stage one:
     * - Collect data for the configuration file
     * - Create .env file
     * - Install the database
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function stageOne()
    {
        $this->section('Local configurations:');

        $validDbDrivers = $this->getValidDbDrivers();
        $this->askQuestions([
            'dbDriver' => ['choice', ['Select a database driver', $validDbDrivers, 0]],
            'dbHost' => 'Enter the database host',
            'dbName' => 'Enter the database name',
            'dbUser' => 'Enter the database username',
            'dbPass' => 'Enter the database password',
            'dbPrefix' => 'Enter the tables prefix',
            'sysEmail' => 'Email address used by the Tiny Issue to send emails from',
            'sysName' => 'Email name used by the Tiny Issue for the email address above',
            'timezone' => 'The application timezone. Find your timezone from: http://php.net/manual/en/timezones.php)',
        ]);
        $this->data['key'] = md5(str_random(40));
        $this->data['dbDriver'] = substr($this->data['dbDriver'], 4);

        // Create .env from .env.example and populate with user data
        $filesystem = $this->getFilesystem();
        $content = $filesystem->read('.env.example');
        if (empty($content)) {
            throw new \Exception('Unable to read .env.example to create .env file.');
        }

        $dbPass = $this->getInputValue('dbPass');
        foreach ($this->data as $key => $value) {
            $value = $key == 'dbPass' ? $dbPass : $value;
            $content = str_replace('{' . $key . '}', $value, $content);
        }
        if ($filesystem->has('.env')) {
            $filesystem->delete('.env');
        }
        $filesystem->put('.env', $content);

        // Update the current database connection
        $config = \Config::get('database.connections.' . $this->data['dbDriver']);
        $config['driver'] = $this->data['dbDriver'];
        $config['host'] = $this->data['dbHost'];
        $config['database'] = $this->data['dbName'];
        $config['username'] = $this->data['dbUser'];
        $config['password'] = $dbPass;
        $config['prefix'] = $this->data['dbPrefix'];
        \Config::set('database.connections.' . $this->data['dbDriver'], $config);
        \Config::set('database.default', $this->data['dbDriver']);

        // Install the new database
        $this->section('Setting up the database:');
        Artisan::call('migrate:install');
        Artisan::call('migrate', ['--force' => true]);
        $this->info('Database created successfully.');
    }

    /**
     * Prints out a section title
     *
     * @param string $title
     *
     * @return void
     */
    protected function section($title)
    {
        $this->line('');
        $this->info($title);
        $this->line('------------------------');
    }

    /**
     * Ask user questions
     *
     * @param array $questions
     *
     * @return $this
     */
    protected function askQuestions(array $questions)
    {
        $labelFormat = function ($label, $value) {
            return sprintf('%s: (%s)', $label, $value);
        };

        foreach ($questions as $name => $question) {
            if (is_array($question)) {
                $question[1][0] = $labelFormat($question[1][0], $this->data[$name]);
                $this->data[$name] = call_user_func_array([$this, $question[0]], $question[1]);
            } else {
                $question = $labelFormat($question, $this->data[$name]);
                $this->data[$name] = $this->ask($question, $this->data[$name]);
            }
        }

        return $this;
    }

    /**
     * Returns an object for application file system
     *
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem(new Adapter(base_path()));
        }

        return $this->filesystem;
    }

    /**
     * Stage two:
     * - Collect details for admin user
     * - Create the admin user
     *
     * @return void
     */
    protected function stageTwo()
    {
        $this->section('Setting up the admin account:');

        $this->askQuestions([
            'adminEmail' => 'Email address',
            'adminFirstName' => 'First Name',
            'adminLastName' => 'Last Name',
            'adminPass' => 'Password',
        ]);

        Model\User::updateOrCreate(['email' => $this->data['adminEmail']], [
            'email' => $this->data['adminEmail'],
            'firstname' => $this->data['adminFirstName'],
            'lastname' => $this->data['adminLastName'],
            'password' => \Hash::make($this->data['adminPass']),
            'deleted' => Model\User::NOT_DELETED_USERS,
            'role_id' => 4,
            'language' => 'en',
        ]);

        $this->info('Admin account created successfully.');
    }

    /**
     * Returns the actual value of user input
     *
     * @param $name
     *
     * @return string
     */
    protected function getInputValue($name)
    {
        return $this->data[$name] === self::EMPTY_VALUE ? '' : $this->data[$name];
    }
}
