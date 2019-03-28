<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201809\cm\Language;
use Google\AdsApi\AdWords\v201809\cm\NetworkSetting;
use Google\AdsApi\AdWords\v201809\cm\Paging;
use Google\AdsApi\AdWords\v201809\o\AttributeType;
use Google\AdsApi\AdWords\v201809\o\IdeaType;
use Google\AdsApi\AdWords\v201809\o\LanguageSearchParameter;
use Google\AdsApi\AdWords\v201809\o\NetworkSearchParameter;
use Google\AdsApi\AdWords\v201809\o\RelatedToQuerySearchParameter;
use Google\AdsApi\AdWords\v201809\o\RequestType;
use Google\AdsApi\AdWords\v201809\o\SeedAdGroupIdSearchParameter;
use Google\AdsApi\AdWords\v201809\o\TargetingIdeaSelector;
use Google\AdsApi\AdWords\v201809\o\TargetingIdeaService;
use Google\AdsApi\Common\OAuth2TokenBuilder;


/**
 * This example gets keyword ideas related to a seed keyword.
 */
class Api
{

    // If you do not want to use an existing ad group to seed your request, you
    // can set this to null.
    const AD_GROUP_ID = null;
    const PAGE_LIMIT = 500;

    public static function runExample(
        AdWordsServices $adWordsServices,
        AdWordsSession $session,
        $adGroupId
    ) {
        $targetingIdeaService = $adWordsServices->get($session, TargetingIdeaService::class);

        // Create selector.
        $selector = new TargetingIdeaSelector();
        $selector->setRequestType(RequestType::STATS);
        $selector->setIdeaType(IdeaType::KEYWORD);
        $selector->setRequestedAttributeTypes(
            [
                AttributeType::KEYWORD_TEXT,
                AttributeType::SEARCH_VOLUME,
                //AttributeType::AVERAGE_CPC,
                //AttributeType::COMPETITION,
                //AttributeType::CATEGORY_PRODUCTS_AND_SERVICES
            ]
        );

        $paging = new Paging();
        $paging->setStartIndex(0);
        $paging->setNumberResults(10);
        $selector->setPaging($paging);

        $searchParameters = [];
        // Create related to query search parameter.
        $relatedToQuerySearchParameter = new RelatedToQuerySearchParameter();


        // database query to get search_term 

        $search_term = DB::table('search_terms')->get();

       	foreach ($search_term as $value) 
       	{
		    $search_term_array[] = $value->name;
		}






        $relatedToQuerySearchParameter->setQueries($search_term_array);
        $searchParameters[] = $relatedToQuerySearchParameter;

        // Create language search parameter (optional).
        // The ID can be found in the documentation:
        // https://developers.google.com/adwords/api/docs/appendix/languagecodes
        $languageParameter = new LanguageSearchParameter();
        $english = new Language();
        $english->setId(1000);
        $languageParameter->setLanguages([$english]);
        $searchParameters[] = $languageParameter;

        // Create network search parameter (optional).
        $networkSetting = new NetworkSetting();
        $networkSetting->setTargetGoogleSearch(true);
        $networkSetting->setTargetSearchNetwork(false);
        $networkSetting->setTargetContentNetwork(false);
        $networkSetting->setTargetPartnerSearchNetwork(false);

        $networkSearchParameter = new NetworkSearchParameter();
        $networkSearchParameter->setNetworkSetting($networkSetting);
        $searchParameters[] = $networkSearchParameter;

        // Optional: Use an existing ad group to generate ideas.
        if (!empty($adGroupId)) {
            $seedAdGroupIdSearchParameter = new SeedAdGroupIdSearchParameter();
            $seedAdGroupIdSearchParameter->setAdGroupId($adGroupId);
            $searchParameters[] = $seedAdGroupIdSearchParameter;
        }
        $selector->setSearchParameters($searchParameters);
        $selector->setPaging(new Paging(0, self::PAGE_LIMIT));

        // Get keyword ideas.
        $page = $targetingIdeaService->get($selector);

        // Print out some information for each targeting idea.
        $entries = $page->getEntries();
        	
      
        return $entries;	

        
    }

    public  function index()
    {
        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile(config('app.adsapi_php_path'))->build();

        // Construct an API session configured from a properties file and the
        // OAuth2 credentials above.
        $session = (new AdWordsSessionBuilder())->fromFile(config('app.adsapi_php_path'))->withOAuth2Credential($oAuth2Credential)->build();
        return self::runExample(new AdWordsServices(), $session, self::AD_GROUP_ID);
    }
}










//config('app.adsapi_php_path') /use this to access file /




//config('app.adsapi_php_path') /use this to access file /
