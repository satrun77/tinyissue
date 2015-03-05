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
use League\Flysystem\Adapter\Local as Adapter;
use League\Flysystem\Filesystem;
use Tinyissue\Model;
use Illuminate\Support\Facades\Artisan;

/**
 * Install is console command to install the Tiny Issue application
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Install extends Command
{
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
        'pdo',
        'mcrypt',
        'openssl',
        'curl',
        'json',
        'mbstring',
    ];

    /**
     * Minimum PHP version
     *
     * @var string
     */
    protected $phpVersion = '5.4.0';

    /**
     * Supported drivers
     *
     * @var array
     */
    protected $dbDrivers = [
        'sqlite',
        'mysql',
        'pgsql',
        'sqlsrv',
    ];

    /**
     * Current enabled drivers
     *
     * @var array
     */
    protected $validDbDrivers = [];

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
        'dbPass' => 'root',
        'dbDriver' => 'mysql',
        'dbPrefix' => '',
        'sysEmail' => '',
        'sysName' => '',
        'adminEmail' => '',
        'adminFirstName' => '',
        'adminLastName' => '',
        'adminPass' => '',
    ];

    protected $filesystem;

    /**
     * Execute the console command.
     *
     * @return mixed
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

        $this->line("<fg=green>Instalation complete.</fg=green>");

        return true;
    }

    /**
     * Check the current environment and display the result in table
     *
     * @return bool
     */
    protected function checkEnvironment()
    {
        $requirements = [];
        $allOk = true;

        // Check PHP modules
        array_walk($this->modules, function ($module) use (&$requirements, &$allOk) {
            if (!extension_loaded($module)) {
                $requirements[] = $this->formatTableCells([$module.' extension', 'No'], 'red');
                $allOk = false;
            } else {
                $requirements[] = $this->formatTableCells([$module.' extension', 'OK'], 'green');
            }
        });

        // Check db drivers
        array_walk($this->dbDrivers, function ($driver) use (&$requirements, &$allOk) {
            if (!extension_loaded('pdo_'.$driver)) {
                $requirements[] = $this->formatTableCells([$driver.' driver for pdo', 'Not Found'], 'blue');
            } else {
                $this->validDbDrivers[] = $driver;
                $requirements[] = $this->formatTableCells([$driver.' driver for pdo', 'OK'], 'green');
            }
        });

        // Whether or not one or more valid drivers were found
        if (false === $this->validDbDrivers) {
            $requirements[] = $this->formatTableCells([
                'Install one of the following pdo drivers ('.implode(', ',
                    $this->dbDrivers).')',
                'Not Found',
            ], 'red');
            $allOk = false;
        } else {
            $requirements[] = $this->formatTableCells([
                'You can choose one of the following pdo drivers ('.implode(', ',
                    $this->validDbDrivers).')',
                'OK',
            ], 'green');
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, $this->phpVersion, '<')) {
            $allOk = false;
            $requirements[] = $this->formatTableCells([
                'PHP version '.$this->phpVersion.' or above is needed.',
                'Not Found',
            ], 'red');
        } else {
            $requirements[] = $this->formatTableCells([
                'PHP version '.$this->phpVersion.' or above is needed.',
                'OK',
            ], 'green');
        }

        // Check application upload directory
        if (!is_writable(base_path('storage/app/uploads'))) {
            $allOk = false;
            $requirements[] = $this->formatTableCells([
                'Upload directory is writable.',
                'No',
            ], 'red');
        } else {
            $requirements[] = $this->formatTableCells([
                'Upload directory is writable.',
                'OK',
            ], 'green');
        }

        // Check if upload directory is accessible to the public
        $pathSegments = explode('/', base_path());
        $count = count($pathSegments);
        $indexes = [];
        $break = false;
        for ($i = 0; $i < $count; $i++) {
            $indexes[] = $i;
            $path = implode('/', array_except($pathSegments, $indexes));
            $guessUrl = url($path.'/storage/app/uploads');
            $curl = curl_init($guessUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $info = curl_getinfo($curl);
            if ($info['http_code'] != '404') {
                $break = true;
                $requirements[] = $this->formatTableCells([
                    'Upload directory maybe accessible from ('.$guessUrl.').',
                    'No',
                ], 'red');
            }
            curl_close($curl);
            if ($break) {
                break;
            }
        }

        // Display the result table
        $this->table(['Requirement', 'Status'], $requirements);

        return $allOk;
    }

    /**
     * Format cell text color
     *
     * @param $cells array
     * @param $color string
     *
     * @return array
     */
    protected function formatTableCells(array $cells, $color)
    {
        return array_map(function ($cell) use ($color) {
            return '<fg='.$color.'>'.$cell.'</fg='.$color.'>';
        }, $cells);
    }

    /**
     * Start a stage loop
     *
     * @param $method string The method name to execute in a loop
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
     * Stage one:
     * - Collect data for the configuration file
     * - Create .env file
     * - Install the database
     *
     * @return void
     */
    protected function stageOne()
    {
        $this->section('Local configurations:');
        $this->data['dbDriver'] = $this->choice('Select a database driver: ('.$this->data['dbDriver'].')',
            $this->validDbDrivers, $this->validDbDrivers[0]);
        $this->data['dbHost'] = $this->ask('Enter the database host: ('.$this->data['dbHost'].')',
            $this->data['dbHost']);
        $this->data['dbName'] = $this->ask('Enter the database name: ('.$this->data['dbName'].')',
            $this->data['dbName']);
        $this->data['dbUser'] = $this->ask('Enter the database username: ('.$this->data['dbUser'].')',
            $this->data['dbUser']);
        $this->data['dbPass'] = $this->ask('Enter the database password: ('.$this->data['dbPass'].')',
            $this->data['dbPass']);
        $this->data['dbPrefix'] = $this->ask('Enter the tables prefix: ('.$this->data['dbPrefix'].')',
            $this->data['dbPrefix']);
        $this->data['sysEmail'] = $this->ask('Email address used by the Tiny Issue to send emails from: ('.$this->data['sysEmail'].')',
            $this->data['sysEmail']);
        $this->data['sysName'] = $this->ask('Email name used by the Tiny Issue for the email address above: ('.$this->data['sysName'].')',
            $this->data['sysName']);
        $this->data['timezone'] = $this->ask('The application timezone. Find your timezone from: http://php.net/manual/en/timezones.php): ('.$this->data['timezone'].')',
            $this->data['timezone']);
        $this->data['key'] = md5(str_random(40));

        // Create .env from .env.example and populate with user data
        $filesystem = $this->getFilesystem();
        $content = $filesystem->read('.env.example');
        foreach ($this->data as $key => $value) {
            $content = str_replace('{'.$key.'}', $value, $content);
        }
        if ($filesystem->has('.env')) {
            $filesystem->delete('.env');
        }
        $filesystem->put('.env', $content);

        // Update the current database connection
        $config = \Config::get('database.connections.'.$this->data['dbDriver']);
        $config['driver'] = $this->data['dbDriver'];
        $config['host'] = $this->data['dbHost'];
        $config['database'] = $this->data['dbName'];
        $config['username'] = $this->data['dbUser'];
        $config['password'] = $this->data['dbPass'];
        $config['prefix'] = $this->data['dbPrefix'];

        \Config::set("database.connections.".$this->data['dbDriver'], $config);
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
     * @param $title string
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
     * Stage two:
     * - Collect details for admin user
     * - Create the admin user
     *
     * @return void
     */
    protected function stageTwo()
    {
        $this->section('Setting up the admin account:');

        $this->data['adminEmail'] = $this->ask('Email address: ('.$this->data['adminEmail'].')',
            $this->data['adminEmail']);
        $this->data['adminFirstName'] = $this->ask('First Name: ('.$this->data['adminFirstName'].')',
            $this->data['adminFirstName']);
        $this->data['adminLastName'] = $this->ask('Last Name: ('.$this->data['adminLastName'].')',
            $this->data['adminLastName']);
        $this->data['adminPass'] = $this->ask('Password: ('.$this->data['adminPass'].')',
            $this->data['adminPass']);

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
}
