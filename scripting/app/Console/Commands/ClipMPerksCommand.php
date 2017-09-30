<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

class ClipMPerksCommand extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:clip-mperks {--unclip-all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically Clip Meijer mPerks Coupons';

    
    protected $client;
    
    protected $keywords;
    
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
        $cookieJar = new \GuzzleHttp\Cookie\CookieJar;
        
        $this->client = new Client([
            'cookies' => true
        ]);
        
        $this->info("Loading Coupon Keyword Database");
        
        $this->keywords = \App\Models\MPerksKeywords::all();
        
        $this->info("Logging Into mPerks....");
        
        $response = $this->client->request('GET', 'https://mperks.meijer.com/mperks/Account/SignIn', [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
            ]
        ]);
        
        sleep(1);
        
        $response = $this->fastForwardRequest($response);

        if(!strstr($response->getBody()->__toString(), "/manage/Credentials/MPerksLogin")) {
            $this->error("Unexpected Result");
            return 1;
        }
        
        $this->info("Preparing to Log in.....");
        
        $response = $this->client->request('POST', 'https://accounts.meijer.com/manage/Credentials/MeijerLogin', [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
            ],
            'form_params' => [
                'autoPopEmail' => 'john@coggeshall.org',
                'email' => 'john@coggeshall.org',
                'hideEmail' => 'john@coggeshall.org',
                'password' => '4Ph0enix',
                'pin' => '',
                'rememberMe' => 'true',
                'showSuccess' => 'false',
                'topSuccessMessage' => '',
                'userPhoneNumber' => ''
            ]
        ]);
        
        sleep(1);
        
        $loginResponse = json_decode($response->getBody()->__toString(), true);
        
        if(!is_array($loginResponse) || !$loginResponse['Success']) {
            $this->error("Failed to Log In!");
            return 1;
        }
        
        $this->info("Successfully Logged In. Redirecting");
        
        $response = $this->client->request('GET', $loginResponse['RedirectUrl'], [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
            ]
        ]);
        
        sleep(1);
        
        if($this->option('unclip-all')) {
            return $this->doUnclipping();
        }
        
        $this->doCouponClipping();
    }
    
    protected function doUnclipping() 
    {
        $this->info("Retrieving Clipped Coupons");
        
        $response = $this->client->request('POST', 'https://mperks.meijer.com/mperks/api/CouponApi/PostFilteredClippedCustomerCoupons', [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
            ],
            'json' => [
                'AdminFilter' => 2,
                'CategoryId' => "",
                'CouponsRetreivalDate' => '1/1/0001 12:00:00 AM',
                'CurrentPage' => 1,
                'HideAutoPopup' => false,
                'IsDepartmentBundle' => true,
                'IsPaginationClicked' => false,
                'NoCouponsClipped' => 0,
                'PageSize' => 48,
                'RewardCouponId' => 0,
                'RewardSourceId' => 0,
                'SearchCriteria' => "",
                'ShowClippedCoupons' => false,
                'SortType' => 0,
                'SubcategoryId' => '',
                'Tab' => 0,
                'TagName' => '',
                'TargetType' => 3,
                'displayFlag' => false,
                'getCount' => true,
                'isTabChanged' => true,
                'showBannerRowCount' => 15
            ]
        ]);
        
        $couponResponse = json_decode($response->getBody()->__toString(), true);
        
        if(!isset($couponResponse['TotalCount'])) {
            $this->error("Unexpected Response");
            return 1;
        }
        
        $totalPages = ceil($couponResponse['TotalCount'] / $couponResponse['PageSize']);
        
        $this->info("There are a total of $totalPages pages of clipped coupons ({$couponResponse['TotalCount']} coupons)");
        
        $clippableCoupons = [];
        
        sleep(1);
        
        for($i = 1; $i <= $totalPages; $i++) {
            
            if(!isset($couponResponse['CouponsList'])) {
                $this->warn("Failed to get list of Coupons for Page $i");
            } else {
                foreach($couponResponse['CouponsList'] as $coupon) {
                    
                    $this->info("Found Coupon: {$coupon['Title']} : {$coupon['Description']}");
                    
                    $clippableCoupons[] = [
                        'id' => $coupon['CouponId'],
                        'source_id' => $coupon['SourceId'],
                        'name' => "{$coupon['Title']} : {$coupon['Description']}"
                        ];
                }
            }
            
            $response = $this->client->request('POST', 'https://mperks.meijer.com/mperks/api/CouponApi/PostFilteredClippedCustomerCoupons', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
                ],
                'json' => [
                    'AdminFilter' => 2,
                    'CategoryId' => "",
                    'CouponsRetreivalDate' => '1/1/0001 12:00:00 AM',
                    'CurrentPage' => $i,
                    'HideAutoPopup' => false,
                    'IsDepartmentBundle' => true,
                    'IsPaginationClicked' => false,
                    'NoCouponsClipped' => 0,
                    'PageSize' => 48,
                    'RewardCouponId' => 0,
                    'RewardSourceId' => 0,
                    'SearchCriteria' => "",
                    'ShowClippedCoupons' => false,
                    'SortType' => 0,
                    'SubcategoryId' => '',
                    'Tab' => 0,
                    'TagName' => '',
                    'TargetType' => 3,
                    'displayFlag' => false,
                    'getCount' => true,
                    'isTabChanged' => true,
                    'showBannerRowCount' => 15
                ]
            ]);
            
            $couponResponse = json_decode($response->getBody()->__toString(), true);
            
            sleep(1);
        }
        
        $this->info("Found " . count($clippableCoupons) . " coupons we can un-clip");
        
        foreach($clippableCoupons as $coupon) {
            $response = $this->client->request('POST', 'https://mperks.meijer.com/mperks/api/CouponApi/PostUnClipCoupon', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
                ],
                'json' => [
                    'CouponId' => $coupon['id'],
                    'SourceId' => $coupon['source_id']
                ]
            ]);
            
            $retval = json_decode($response->getBody()->__toString(), true);
            
            if(!isset($retval['IsSuccess']) || !$retval['IsSuccess']) {
                
                switch($retval['ErrorMessage']) {
                    case 'OfferNotClipped':
                        sleep(1);
                        continue 2;
                }
                
                $this->error("Failed to Clip Coupon ID {$coupon['id']} : {$coupon['name']}");
                $this->error("Error Code: {$retval['ErrorMessage']}");
                sleep(1);
                continue ;
                
            } else {
                $this->info("Un-Clipped {$coupon['name']}");
            }
            
            sleep(1);
        }
        
    }
    protected function doCouponClipping()
    {
        $this->info("Retrieving Coupons...");
        
        $response = $this->client->request('POST', 'https://mperks.meijer.com/mperks/api/CouponApi/PostFilteredCustomerCoupons', [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
            ],
            'json' => [
                'AdminFilter' => 2,
                'CategoryId' => "",
                'CouponsRetreivalDate' => '1/1/0001 12:00:00 AM',
                'CurrentPage' => 1,
                'HideAutoPopup' => false,
                'IsDepartmentBundle' => true,
                'IsPaginationClicked' => false,
                'NoCouponsClipped' => 0,
                'PageSize' => 48,
                'RewardCouponId' => 0,
                'RewardSourceId' => 0,
                'SearchCriteria' => "",
                'ShowClippedCoupons' => false,
                'SortType' => "3",
                'SubcategoryId' => '',
                'Tab' => 0,
                'TagName' => '',
                'TargetType' => 3,
                'displayFlag' => false,
                'getCount' => true,
                'isTabChanged' => true,
                'showBannerRowCount' => 15
            ]
        ]);
        
        $couponResponse = json_decode($response->getBody()->__toString(), true);
        
        if(!isset($couponResponse['TotalCount'])) {
            $this->error("Unexpected Response");
            return 1;
        }
        
        $totalPages = ceil($couponResponse['TotalCount'] / $couponResponse['PageSize']);
        
        $this->info("There are a total of $totalPages pages of coupons ({$couponResponse['TotalCount']} coupons)");
        
        $clippableCoupons = [];
        
        sleep(1);
        
        $allCoupons = [];
        
        for($i = 1; $i <= $totalPages; $i++) {
            
            
            if(!isset($couponResponse['CouponsList'])) {
                $this->warn("Failed to get list of Coupons for Page $i");
            } else {
                
                $allCoupons = array_merge($allCoupons, $couponResponse['CouponsList']);
                
                foreach($couponResponse['CouponsList'] as $coupon) {
                    
                    if($coupon['IsDummy']) {
                        continue;
                    }
                    
                    if($coupon['DisableClip']) {
                        continue;
                    }
                    
                    if($coupon['IsClipped']) {
                        continue;
                    }
                    
                    if($coupon['IsClippedReload']) {
                        continue;
                    }
                    
                    $couponName = "{$coupon['Title']} : {$coupon['Description']}";
                    
                    if($this->couponMatchesKeywords($couponName)) {
                    
                        $this->info("Found Coupon Matching Keywords: $couponName");
                        
                        $clippableCoupons[] = [
                            'id' => $coupon['CouponId'],
                            'source_id' => $coupon['SourceId'],
                            'name' => $couponName
                            ];
                    }
                }
            }
            
            $response = $this->client->request('POST', 'https://mperks.meijer.com/mperks/api/CouponApi/PostFilteredCustomerCoupons', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
                ],
                'json' => [
                    'AdminFilter' => 2,
                    'CategoryId' => "",
                    'CouponsRetreivalDate' => '1/1/0001 12:00:00 AM',
                    'CurrentPage' => $i,
                    'HideAutoPopup' => false,
                    'IsDepartmentBundle' => true,
                    'IsPaginationClicked' => false,
                    'NoCouponsClipped' => 0,
                    'PageSize' => 48,
                    'RewardCouponId' => 0,
                    'RewardSourceId' => 0,
                    'SearchCriteria' => "",
                    'ShowClippedCoupons' => false,
                    'SortType' => "3",
                    'SubcategoryId' => '',
                    'Tab' => 0,
                    'TagName' => '',
                    'TargetType' => 3,
                    'displayFlag' => false,
                    'getCount' => true,
                    'isTabChanged' => true,
                    'showBannerRowCount' => 15
                ]
            ]);
            
            $couponResponse = json_decode($response->getBody()->__toString(), true);
            
            sleep(1);
        }
        
        $this->info("Total Coupons Procsesed:" . count($allCoupons));
        
        $this->info("Found " . count($clippableCoupons) . " coupons we can clip");
        
        foreach($clippableCoupons as $coupon) {
            $response = $this->client->request('POST', 'https://mperks.meijer.com/mperks/api/CouponApi/PostClipCoupon', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
                ],
                'json' => [
                    'CouponId' => $coupon['id'],
                    'SourceId' => $coupon['source_id']
                ]
            ]);
            
            $retval = json_decode($response->getBody()->__toString(), true);
            
            if(!isset($retval['IsSuccess']) || !$retval['IsSuccess']) {
                
                switch($retval['ErrorMessage']) {
                    case 'ShopperClipLimitExceeded':
                        $this->info("Reached Maximum Clipping Capacity, All done!");
                        return;
                        break;
                    case 'OfferAlreadyClipped':
                        sleep(1);
                        continue 2;
                }
                
                $this->error("Failed to Clip Coupon ID {$coupon['id']} : {$coupon['name']}");
                $this->error("Error Code: {$retval['ErrorMessage']}");
                sleep(1);
                continue ;
                
            } else {
                $this->info("Clipped {$coupon['name']}");
            }
            
            sleep(1);
        }
        
    }
    
    protected function fastForwardRequest(\GuzzleHttp\Psr7\Response $response) {
        
        $responseContent = $response->getBody()->__toString();
        
        if(strstr($responseContent, "form.autopost.template")) {

            $qp = html5qp($responseContent);
            
            $resumePath = $qp->find("input[name='resumePath']")->attr('value');
            $postUri = $qp->find("form")->attr('action');
            
            $this->info("Fast fowarding...");
            
            $response = $this->client->request('POST', $postUri, [
                'form_params' => [
                    'allowInteraction' => "true",
                    'resumePath' => $resumePath,
                    'reauth' => "false"
                ],
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
                ]
            ]);
            
            return $this->fastForwardRequest($response);
        }
        
        return $response;
    }
    
    protected function couponMatchesKeywords($name)
    {
        foreach($this->keywords as $keywordList) {
            
            $keywords = explode(",", $keywordList->keywords);
            
            $searchRegex = "/^";
            
            foreach($keywords as $keyword) {
                $keyword = strtolower(trim($keyword));
                $searchRegex .= "(?=.*\b$keyword\b)";
            }
            
            $searchRegex .= ".+/i";
            
            if(preg_match($searchRegex, $name)) {
                return true;
            }
        }
        
        return false;
    }
}
