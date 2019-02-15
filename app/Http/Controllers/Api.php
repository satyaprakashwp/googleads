<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201806\cm\CampaignService;
use Google\AdsApi\AdWords\v201806\cm\OrderBy;
use Google\AdsApi\AdWords\v201806\cm\Paging;
use Google\AdsApi\AdWords\v201806\cm\Selector;
use Google\AdsApi\AdWords\v201806\cm\SortOrder;
use Google\AdsApi\AdWords\v201806\o\TargetingIdeaSelector;
use Google\AdsApi\AdWords\v201806\o\RequestType;
use Google\AdsApi\AdWords\v201806\o\IdeaType;
use Google\AdsApi\AdWords\v201806\o\AttributeType;
use Google\AdsApi\AdWords\v201806\o\RelatedToQuerySearchParameter;
use Google\AdsApi\AdWords\v201806\o\TargetingIdeaService;
use Google\AdsApi\Common\OAuth2TokenBuilder;

class Api extends Controller 
{
	public function index()
	{
		$selector = new TargetingIdeaSelector();
		$selector->setRequestType(RequestType::IDEAS);
		$selector->setIdeaType(IdeaType::KEYWORD);

		$selector->setRequestedAttributeTypes(
		    [
		        AttributeType::KEYWORD_TEXT,
		        AttributeType::SEARCH_VOLUME,
		        AttributeType::AVERAGE_CPC,
		        AttributeType::COMPETITION,
		        AttributeType::CATEGORY_PRODUCTS_AND_SERVICES
		    ]
		);

		$paging = new Paging();
        $paging->setStartIndex(0);
        $paging->setNumberResults(10);
        $selector->setPaging($paging);

        $searchParameters = [];
        // Create related to query search parameter.
		$relatedToQuerySearchParameter = new RelatedToQuerySearchParameter();
		$relatedToQuerySearchParameter->setQueries(
		    [
		        'bakery',
		        'pastries',
		        'birthday cake'
		    ]
		);
        $searchParameters[] = $relatedToQuerySearchParameter;

        // Get keyword ideas.
        $targetingIdeaService = new TargetingIdeaService();
        $page = $targetingIdeaService->get($selector);

        // Print out some information for each targeting idea.
		$entries = $page->getEntries();
		if ($entries !== null) {
		    foreach ($entries as $targetingIdea) {
		        $data = MapEntries::toAssociativeArray($targetingIdea->getData());
		        $keyword = $data[AttributeType::KEYWORD_TEXT]->getValue();
		        $searchVolume = ($data[AttributeType::SEARCH_VOLUME]->getValue() !== null)
		            ? $data[AttributeType::SEARCH_VOLUME]->getValue() : 0;
		        $averageCpc = $data[AttributeType::AVERAGE_CPC]->getValue();
		        $competition = $data[AttributeType::COMPETITION]->getValue();
		        $categoryIds = ($data[AttributeType::CATEGORY_PRODUCTS_AND_SERVICES]->getValue() === null)
		            ? $categoryIds = ''
		            : implode(
		                ', ',
		                $data[AttributeType::CATEGORY_PRODUCTS_AND_SERVICES]->getValue()
		            );
		        printf(
		            "Keyword with text '%s', average monthly search volume %d, "
		            . "average CPC %d, and competition %.2f was found with categories: %s\n",
		            $keyword,
		            $searchVolume,
		            ($averageCpc === null) ? 0 : $averageCpc->getMicroAmount(),
		            $competition,
		            $categoryIds
		        );
		    }
        }
	}
}