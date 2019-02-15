<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api;

class googleApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googleApi:getSearchVolume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is the google ads API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       //$this->info('done@@@');
        $controller = new Api(); // make sure to import the controller
        $controller->index();
    }
}