<?php

namespace OrisIntel\AuditLog\Console\Commands;

use Illuminate\Console\Command;

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

        $model = new $class;
        $table = $model->getTable();


    }
}
