<?php

namespace OrisIntel\AuditLog\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;

class MakeModelAuditLogTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:model-auditlog 
                                {existing-model-class : Define which model this auditlog should extend.}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes a new audit log migration and model to your application.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = $this->argument('existing-model-class');

        if (! class_exists($class)) {
            $this->error("Class {$class} could not be found");

            return;
        }

        $this->line("Generating audit log model and table migration for: $class");

        $subject_model = new $class();
        $config = config('model-auditlog');

        $this->line("Audit Table will be {$this->generateAuditTableName($subject_model, $config)}");
        $this->createMigration($subject_model, $config);

        $this->line("Audit Model will be {$this->generateAuditModelName($subject_model, $config)}");
        $this->createModel($subject_model, $config);
    }

    /**
     * @param Model $subject_model
     * @param array $config
     *
     * @return string
     */
    public function generateAuditTableName($subject_model, array $config) : string
    {
        return $subject_model->getTable() . $config['table_suffix'];
    }

    /**
     * @param Model $subject_model
     * @param array $config
     *
     * @return string
     */
    public function generateAuditModelName($subject_model, array $config) : string
    {
        return class_basename($subject_model) . $config['model_suffix'];
    }

    /**
     * @param Model $subject_model
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    public function getModelNamespace($subject_model) : string
    {
        return (new ReflectionClass($subject_model))->getNamespaceName();
    }

    /**
     * @param Model $subject_model
     * @param array $config
     *
     * @throws \ReflectionException
     */
    public function createModel($subject_model, array $config) : void
    {
        $modelname = $this->generateAuditModelName($subject_model, $config);

        $stub = $this->getStubWithReplacements($config['model_stub'], [
            '{TABLE_NAME}' => $this->generateAuditTableName($subject_model, $config),
            '{CLASS_NAME}' => $modelname,
            '{NAMESPACE}'  => $this->getModelNamespace($subject_model),
        ]);

        $filename = $config['model_path'] . DIRECTORY_SEPARATOR . $modelname . '.php';

        if (file_put_contents($filename, $stub)) {
            $this->info("Model successfully created at: $filename");
        }
    }

    /**
     * @param Model $subject_model
     * @param array $config
     */
    public function createMigration($subject_model, array $config) : void
    {
        $tablename = $this->generateAuditTableName($subject_model, $config);
        $fileslug = "create_{$tablename}_table";

        $stub = $this->getStubWithReplacements($config['migration_stub'], [
            '{TABLE_NAME}'          => $tablename,
            '{CLASS_NAME}'          => $this->generateMigrationClassname($fileslug),
            '{PROCESS_IDS_SETUP}'   => $this->generateMigrationProcessStamps($config),
            '{FOREIGN_KEY_SUBJECT}' => $this->generateMigrationSubjectForeignKeys($subject_model, $config),
            '{FOREIGN_KEY_USER}'    => $this->generateMigrationUserForeignKeys($config),
        ]);

        $filename = $config['migration_path'] . DIRECTORY_SEPARATOR . $this->generateMigrationFilename($fileslug);

        if (file_put_contents($filename, $stub)) {
            $this->info("Migration successfully created at: $filename");
        }
    }

    /**
     * @param string $fileslug
     *
     * @return string
     */
    public function generateMigrationFilename(string $fileslug) : string
    {
        return Str::snake(Str::lower(date('Y_m_d_His') . ' ' . $fileslug . '.php'));
    }

    /**
     * @param string $fileslug
     *
     * @return string
     */
    public function generateMigrationClassname(string $fileslug) : string
    {
        return Str::studly($fileslug);
    }

    /**
     * @param string $file
     * @param array  $replacements
     *
     * @return string
     */
    public function getStubWithReplacements(string $file, array $replacements) : string
    {
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            file_get_contents(realpath($file))
        );
    }

    /**
     * @param Model $subject_model
     * @param array $config
     *
     * @return string
     */
    public function generateMigrationSubjectForeignKeys($subject_model, array $config) : string
    {
        if (Arr::get($config, 'enable_subject_foreign_keys') === true) {
            return '$table->foreign(\'subject_id\')
                ->references(\'' . $subject_model->getKeyName() . '\')
                ->on(\'' . $subject_model->getTable() . '\');';
        }

        return '';
    }

    /**
     * @param array $config
     *
     * @return string
     */
    public function generateMigrationUserForeignKeys(array $config) : string
    {
        $user_model = new $config['user_model']();
        if (Arr::get($config, 'enable_user_foreign_keys') === true && ! empty($user_model)) {
            $user_table = $user_model->getTable();
            $user_primary = $user_model->getKeyName();

            return '$table->foreign(\'user_id\')
                ->references(\'' . $user_primary . '\')
                ->on(\'' . $user_table . '\');';
        }

        return '';
    }

    /**
     * @param array $config
     *
     * @return string
     */
    public function generateMigrationProcessStamps(array $config) : string
    {
        if (Arr::get($config, 'enable_process_stamps') === true) {
            return '$table->processIds();';
        }

        return '';
    }
}
