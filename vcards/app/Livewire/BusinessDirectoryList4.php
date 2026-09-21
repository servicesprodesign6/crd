<?php

namespace App\Livewire;

use App\Models\Vcard;
use App\Models\WhatsappStore;
use App\Models\CustomDomain;
use App\Models\Subscription;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

class BusinessDirectoryList4 extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $searchName = '';
    public $searchLocation = '';
    public $filterType = '';
    public $sortType = 'recent';

    public $filterTemplate = '';
    public $filterWpTemplate = '';

    public $templateNames = [
        1 => 'Simple Contact',
        2 => 'Executive Profile',
        3 => 'Clean Canvas',
        4 => 'Professional',
        5 => 'Corporate Connect',
        6 => 'Modern Edge',
        7 => 'Business Beacon',
        8 => 'Corporate Classic',
        9 => 'Corporate Identity',
        10 => 'Pro Network',
        11 => 'Portfolio',
        12 => 'Gym 2',
        13 => 'Hospital 2',
        14 => 'Event Management 2',
        15 => 'Salon 2',
        16 => 'Lawyer 2',
        17 => 'Programmer 2',
        18 => 'CEO/CXO 2',
        19 => 'Fashion Beauty 2',
        20 => 'Culinary Food Services 2',
        21 => 'Social Media 2',
        22 => 'Dynamic vCard 2',
        23 => 'Consulting Services 2',
        24 => 'School Templates 2',
        25 => 'Social Services 2',
        26 => 'Retail E-commerce 2',
        27 => 'Pet Shop 2',
        28 => 'Pet Clinic 2',
        29 => 'Marriage 2',
        30 => 'Taxi Service 2',
        31 => 'Handyman Services 2',
        32 => 'Interior Designer 2',
        33 => 'Musician 2',
        34 => 'Photographer 2',
        35 => 'Real Estate 2',
        36 => 'Travel Agency 2',
        37 => 'Flower Garden 2',
        38 => 'Architecture',
        39 => 'Gym 1',
        40 => 'Hospital 1',
        41 => 'Event Management 1',
        42 => 'Salon 1',
        43 => 'Lawyer 1',
        44 => 'Programmer 1',
        45 => 'CEO/CXO 1',
        46 => 'Fashion Beauty 1',
        47 => 'Culinary Food Services 1',
        48 => 'Social Media 1',
        49 => 'Dynamic vCard 1',
        50 => 'Consulting Services 1',
        51 => 'School Templates 1',
        52 => 'Social Services 1',
        53 => 'Retail E-commerce 1',
        54 => 'Pet Shop 1',
        55 => 'Pet Clinic 1',
        56 => 'Marriage 1',
        57 => 'Taxi Service 1',
        58 => 'Handyman Services 1',
        59 => 'Interior Designer 1',
        60 => 'Musician 1',
        61 => 'Photographer 1',
        62 => 'Real Estate 1',
        63 => 'Travel Agency 1',
        64 => 'Flower Garden 1',
        65 => 'Reporter',
        66 => 'Hotel',
        67 => 'Perfumes & Fragrance',
        68 => 'Phone Accessories',
    ];

    public $templatePairs = [
        39 => 12,
        40 => 13,
        41 => 14,
        42 => 15,
        43 => 16,
        44 => 17,
        45 => 18,
        46 => 19,
        47 => 20,
        48 => 21,
        49 => 22,
        50 => 23,
        51 => 24,
        52 => 25,
        53 => 26,
        54 => 27,
        55 => 28,
        56 => 29,
        57 => 30,
        58 => 31,
        59 => 32,
        60 => 33,
        61 => 34,
        62 => 35,
        63 => 36,
        64 => 37,
    ];

    public $wpTemplateNames = [
        1 => 'Beauty Product',
        2 => 'E-Commerce',
        3 => 'Restaurant',
        4 => 'Grocery',
        5 => 'Cloth Store',
        6 => 'Home Decor',
        7 => 'Jewellery',
        8 => 'Travel',
    ];

    public function updatedSearchName()
    {
        $this->resetPage();
    }

    public function updatedSearchLocation()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
        if ($this->filterType !== 'vcard') {
            $this->filterTemplate = '';
        }

        if ($this->filterType !== 'whatsapp_store') {
            $this->filterWpTemplate = '';
        }
    }

    public function updatedSortType()
    {
        $this->resetPage();
    }

    public function updatedFilterTemplate()
    {
        $this->resetPage();
    }

    public function updatedFilterWpTemplate()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->reset(['filterType', 'searchName', 'searchLocation', 'sortType']);
        $this->resetPage();
    }

    public function getGroupedTemplatesProperty()
    {
        $grouped = [];

        foreach ($this->templateNames as $id => $name) {
            if (isset($this->templatePairs[$id])) {
                continue;
            }

            $pairKey = array_search($id, $this->templatePairs);
            if ($pairKey !== false) {
                $baseName = preg_replace('/\s[12]$/', '', $name);

                $grouped[$id] = [
                    'label' => $baseName,
                    'ids' => [$id, $pairKey],
                ];
            } else {
                $grouped[$id] = [
                    'label' => $name,
                    'ids' => [$id],
                ];
            }
        }
        return $grouped;
    }

    public function render()
    {
        //vcard Query
        $vcardsQuery = Vcard::where('status', '1')->with('tenant.user')->where('enable_business_directory', '1')
            ->whereHas('subscriptions', function ($q) {
                $q->where('status', Subscription::ACTIVE)
                  ->where('ends_at', '>', now());
            });

        // Search name/occupation/job/phone/email/company/SEO for vCards
        if (!empty($this->searchName)) {
            $vcardsQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchName . '%')
                    ->orWhere('occupation', 'like', '%' . $this->searchName . '%')
                    ->orWhere('job_title', 'like', '%' . $this->searchName . '%')
                    ->orWhere('phone', 'like', '%' . $this->searchName . '%')
                    ->orWhere('email', 'like', '%' . $this->searchName . '%')
                    ->orWhere('company', 'like', '%' . $this->searchName . '%')
                    ->orWhere('site_title', 'like', '%' . $this->searchName . '%')
                    ->orWhere('home_title', 'like', '%' . $this->searchName . '%')
                    ->orWhere('meta_keyword', 'like', '%' . $this->searchName . '%')
                    ->orWhere('meta_description', 'like', '%' . $this->searchName . '%');
            });
        }

        if (!empty($this->searchLocation)) {
            $vcardsQuery->where('location', 'like', '%' . $this->searchLocation . '%');
        }

        if ($this->filterType === 'vcard' && !empty($this->filterTemplate)) {

            $group = $this->groupedTemplates[$this->filterTemplate] ?? null;

            if ($group) {
                $vcardsQuery->whereIn('template_id', $group['ids']);
            }
        }

        if ($this->sortType === 'popular') {
            $vcardsQuery->withCount('Analytics as analytics_count');
        } elseif ($this->sortType === 'trending') {
            $vcardsQuery->withCount([
                'Analytics as analytics_count' => function ($q) {
                    $q->where('created_at', '>', now()->subDays(7));
                }
            ]);
        }

        $vcardResults = $vcardsQuery->get();
        $customDomains = CustomDomain::whereIn('user_id', $vcardResults->pluck('tenant.user.id')->filter()->unique())
            ->where('is_active', 1)
            ->get()
            ->keyBy('user_id');

        $vcards = $vcardResults->map(function ($vcard) use ($customDomains) {

            $userId = optional(optional($vcard->tenant)->user)->id;

            $customDomain = $userId ? $customDomains->get($userId) : null;

            $isCustomDomainUse = $customDomain ? $customDomain->is_use_vcard : false;

            $vcard->type = 'vcard';

            $vcard->url = $isCustomDomainUse
                ? "https://{$customDomain->domain}/{$vcard->url_alias}"
                : route('vcard.show', ['alias' => $vcard->url_alias]);

            $vcard->image_url = $vcard->business_directory_url;
            $vcard->video_url = $vcard->cover_url;
            $vcard->cover_type = $vcard->cover_type;
            $vcard->youtube_link = $vcard->youtube_link;
            $vcard->analytics_count = $vcard->analytics_count ?? 0;

            return $vcard;
        });

        // Store Query
        $storeQuery = WhatsappStore::where('status', '1')->where('enable_business_directory', '1')
            ->whereHas('subscriptions', function ($q) {
                $q->where('status', Subscription::ACTIVE)
                  ->where('ends_at', '>', now())
                  ->whereHas('plan.planFeature', function ($pq) {
                      $pq->where('whatsapp_store', 1);
                  });
            });

        // Search store_name/whatsapp_no/SEO for Stores
        if (!empty($this->searchName)) {
            $storeQuery->where(function ($q) {
                $q->where('store_name', 'like', '%' . $this->searchName . '%')
                    ->orWhere('whatsapp_no', 'like', '%' . $this->searchName . '%')
                    ->orWhere('site_title', 'like', '%' . $this->searchName . '%')
                    ->orWhere('home_title', 'like', '%' . $this->searchName . '%')
                    ->orWhere('meta_keyword', 'like', '%' . $this->searchName . '%')
                    ->orWhere('meta_description', 'like', '%' . $this->searchName . '%');
            });
        }

        if (!empty($this->searchLocation)) {
            $storeQuery->where('address', 'like', '%' . $this->searchLocation . '%');
        }

        if ($this->filterType === 'whatsapp_store' && !empty($this->filterWpTemplate)) {
            $storeQuery->where('template_id', $this->filterWpTemplate);
        }

        // Analytics Count for Stores
        if ($this->sortType === 'popular') {
            $storeQuery->withCount('analytics as analytics_count');
        } elseif ($this->sortType === 'trending') {
            $storeQuery->withCount([
                'analytics as analytics_count' => function ($q) {
                    $q->where('created_at', '>', now()->subDays(7));
                }
            ]);
        }

        $whatsappstores = $storeQuery->get()->map(function ($store) {

            $store->type = 'whatsapp_store';
            $store->url = route('whatsapp.store.show', $store->url_alias);
            $store->image_url = !empty($store->cover_url) ? $store->cover_url : $store->logo_url;
            $store->location = $store->address;
            $store->name = $store->store_name;
            $store->analytics_count = $store->analytics_count ?? 0;

            return $store;
        });

        $allBusinesses = $vcards->merge($whatsappstores);

        if (!empty($this->filterType)) {
            $allBusinesses = $allBusinesses->filter(function ($business) {
                return $business->type === $this->filterType;
            });
        }

        // Sorting Logic
        if ($this->sortType === 'popular' || $this->sortType === 'trending') {
            $allBusinesses = $allBusinesses->sortByDesc('analytics_count')->values();
        } else {
            $allBusinesses = $allBusinesses->sortByDesc('created_at')->values();
        }

        $perPage = 12;
        $currentPage = $this->getPage() ?: 1;
        $total = $allBusinesses->count();

        $paginatedBusinesses = new LengthAwarePaginator(
            $allBusinesses->forPage($currentPage, $perPage), $total, $perPage, $currentPage,
            ['path' => request()->url(), 'pageName' => 'page',]
        );

        return view('livewire.business-directory-list4', ['businesses' => $paginatedBusinesses, 'templateNames' => $this->templateNames, 'wpTemplateNames' => $this->wpTemplateNames,]);
    }
}
