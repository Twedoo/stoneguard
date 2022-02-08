<?php namespace Twedoo\StoneGuard;

/**
 * This file is part of StoneGuard,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Twedoo\stoneGuard
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'stoneGuard:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the StoneGuard specifications.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->handle();
    }

    /**
     * Execute the console command for Laravel 5.5+.
     *
     * @return void
     */
    public function handle()
    {
        $this->laravel->view->addNamespace('stoneGuard', substr(__DIR__, 0, -8).'views');

        $rolesTable          = Config::get('stoneGuard.roles_table');
        $roleUserTable       = Config::get('stoneGuard.role_user_table');
        $permissionsTable    = Config::get('stoneGuard.permissions_table');
        $permissionRoleTable = Config::get('stoneGuard.permission_role_table');

        $this->line('');
        $this->info( "Tables: $rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable" );

        $message = "A migration that creates '$rolesTable', '$roleUserTable', '$permissionsTable', '$permissionRoleTable'".
        " tables will be created in database/migrations directory";

        $this->comment($message);
        $this->line('');

        if ($this->confirm("Proceed with the migration creation? [Yes|no]", "Yes")) {

            $this->line('');

            $this->info("Creating migration...");
            if ($this->createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable)) {

                $this->info("Migration successfully created!");
            } else {
                $this->error(
                    "Couldn't create migration.\n Check the write permissions".
                    " within the database/migrations directory."
                );
            }

            $this->line('');

        }
        $this->callSilent('vendor:publish', ['--provider' => 'Twedoo\Stone\StoneServiceProvider']);
        $this->info('Twedoo\Stone was installed successfully.');
    }

    /**
     * Create the migration.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable)
    {
        $migrationFile = base_path("/database/migrations")."/".date('Y_m_d_His')."_stoneGuard_setup_tables.php";

        $userModelName = Config::get('auth.providers.users.model');
        $userModel = new $userModelName();
        $usersTable = $userModel->getTable();
        $userKeyName = $userModel->getKeyName();

        $data = compact('rolesTable', 'roleUserTable', 'permissionsTable', 'permissionRoleTable', 'usersTable', 'userKeyName');

        $output = $this->laravel->view->make('stoneGuard::generators.migration')->with($data)->render();

        if (!file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }
}
