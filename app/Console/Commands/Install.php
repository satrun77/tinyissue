<?php

namespace Tinyissue\Console\Commands;

use Illuminate\Console\Command;
use League\Flysystem\Adapter\Local as Adapter;
use League\Flysystem\Filesystem;
use Tinyissue\Model;
use Illuminate\Support\Facades\Artisan;

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

    protected $modules = [
        'pdo',
        'mcrypt',
        'openssl',
        'curl',
        'json',
        'mbstring',
    ];

    protected $phpVersion = '5.4.0';

    protected $dbDrivers = [
        'sqlite',
        'mysql',
        'pgsql',
        'sqlsrv',
    ];

    protected $validDbDrivers = [];

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

    protected function checkEnvironment()
    {
        $requirements = [];
        $allOk = true;

        array_walk($this->modules, function ($module) use (&$requirements, &$allOk) {
            if (!extension_loaded($module)) {
                $requirements[] = $this->formatTableCells([$module.' extension', 'No'], 'red');
                $allOk = false;
            } else {
                $requirements[] = $this->formatTableCells([$module.' extension', 'OK'], 'green');
            }
        });

        array_walk($this->dbDrivers, function ($driver) use (&$requirements, &$allOk) {
            if (!extension_loaded('pdo_'.$driver)) {
                $requirements[] = $this->formatTableCells([$driver.' driver for pdo', 'Not Found'], 'blue');
            } else {
                $this->validDbDrivers[] = $driver;
                $requirements[] = $this->formatTableCells([$driver.' driver for pdo', 'OK'], 'green');
            }
        });

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

        $segments = explode('/', base_path());
        $count = count($segments);
        $basePath = base_path();
        $indexes = [];
        $break = false;
        for ($i = 0; $i < $count; $i++) {
            $indexes[] = $i;
            $path = implode('/', array_except($segments, $indexes));
            $guessUrl = url($path.'/storage/app/uploads');
            $curl = curl_init($guessUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $data = curl_exec($curl);
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

        $this->table(['Requirement', 'Status'], $requirements);

        return $allOk;
    }

    protected function formatTableCells($cells, $color)
    {
        return array_map(function ($cell) use ($color) {
            return '<fg='.$color.'>'.$cell.'</fg='.$color.'>';
        }, $cells);
    }

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

    protected function getFilesystem()
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem(new Adapter(base_path()));
        }

        return $this->filesystem;
    }

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

        $filesystem = $this->getFilesystem();
        $content = $filesystem->read('.env.example');
        foreach ($this->data as $key => $value) {
            $content = str_replace('{'.$key.'}', $value, $content);
        }
        if ($filesystem->has('.env')) {
            $filesystem->delete('.env');
        }
        $filesystem->put('.env', $content);

        $config = \Config::get('database.connections.'.$this->data['dbDriver']);
        $config['driver'] = $this->data['dbDriver'];
        $config['host'] = $this->data['dbHost'];
        $config['database'] = $this->data['dbName'];
        $config['username'] = $this->data['dbUser'];
        $config['password'] = $this->data['dbPass'];
        $config['prefix'] = $this->data['dbPrefix'];

        \Config::set("database.connections.".$this->data['dbDriver'], $config);
        \Config::set('database.default', $this->data['dbDriver']);

        $this->section('Setting up the database:');
        Artisan::call('migrate:install');
        Artisan::call('migrate', ['--force' => true]);
        $this->info('Database created successfully.');
    }

    protected function section($title)
    {
        $this->line('');
        $this->info($title);
        $this->line('------------------------');
    }

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
