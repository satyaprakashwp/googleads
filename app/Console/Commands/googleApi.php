<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
use Google\AdsApi\AdWords\v201809\o\AttributeType;
use Google\AdsApi\Common\Util\MapEntries;

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
        
            $entries =  $controller->index();// data returned fro, Api controller index function 



             $ProgressBar = $this->output->createProgressBar(count($entries));
             $ProgressBar->setFormat('verbose');   

                 if ($entries !== null) {
                    
                    $sql= 'UPDATE search_terms set search_volume = (case ';
                    
                    $search_term_array=array();

                    foreach ($entries as $targetingIdea) 
                    {
                        $ProgressBar->advance();


                        $data = MapEntries::toAssociativeArray($targetingIdea->getData());
                        $keyword = $data[AttributeType::KEYWORD_TEXT]->getValue();
                        $searchVolume = ($data[AttributeType::SEARCH_VOLUME]->getValue() !== null)
                            ? $data[AttributeType::SEARCH_VOLUME]->getValue() : 0;
                        
                      $sql .= "when name='".$keyword."' then $searchVolume "; 
                      $search_term_array[] = $keyword;

                    }

                    $sql .= ' end) where name in("'.implode("\",\"",$search_term_array).'") ';  
                    
                    

                    $affectedRows = DB::update($sql);
                    if($affectedRows > 0)
                    {
                        $this->info("\n------ $affectedRows Search Volume value has updated into database ------\n");    
                    }else
                    {
                           $this->info("\n------ All Search Volume value are up to date ------\n"); 
                    }
                    

                }

                if (empty($entries)) {
                    $this->info("\n------ No related keywords were found. ------\n");
                }
        
                //testing  of progress bass
                /*$bb = range(0, 1000000);
                $ProgressBar = $this->output->createProgressBar(count($bb));
                $ProgressBar->setFormat('verbose');
                foreach ($bb as $targetingIdea) 
                {
                    $ProgressBar->advance();

                }*/
                //testing  of progress bass   
       


            $ProgressBar->finish();
            echo "\n";


    }
}