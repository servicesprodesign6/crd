<?php

namespace App\Repositories;

use App\Models\Enquiry;
use App\Models\Plan;
use App\Models\ScheduleAppointment;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vcard;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CityRepository
 *
 * @version July 31, 2021, 7:41 am UTC
 */
class DashboardRepository
{
    /**
     * @return \App\Models\Builder|User|User[]|\Illuminate\Database\Eloquent\Builder|Collection
     */
    public function getUsers()
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'super_admin');
        })->get();
    }

    /**
     * @return Vcard[]|Collection
     */
    public function getVcards()
    {
        return Vcard::all();
    }

    /**
     * @return Plan[]|Collection
     */
    public function getPlans()
    {
        return Plan::all();
    }

    /**
     * @return mixed
     */
    public function getVcardsCount()
    {
        $query = Vcard::where('tenant_id', auth()->user()->tenant_id);

        if (isOrganisationUser()) {
            $query->whereHas('assignedUser', function ($q) {
                $q->where('user_id', \getLogInUserId());
            });
        }

        return $query->get();
    }

    /**
     * @return mixed
     */
    public function getWhatsappStoresCount()
    {
        $query = WhatsappStore::where('tenant_id', auth()->user()->tenant_id);

        if (isOrganisationUser()) {
            $query->whereHas('assignedUser', function ($q) {
                $q->where('user_id', \getLogInUserId());
            });
        }

        return $query->get();
    }

    /**
     * @return mixed
     */
    public function getEnquiryCountAttribute()
    {
        $vcardQuery = Vcard::where('tenant_id', auth()->user()->tenant_id);

        if (isOrganisationUser()) {
            $vcardQuery->whereHas('assignedUser', function ($q) {
                $q->where('user_id', getLogInUserId());
            });
        }

        $vcardIds = $vcardQuery->select('id');

        return Enquiry::whereIn('vcard_id', $vcardIds)->whereDate('created_at', \Carbon\Carbon::today())->count();
    }

    /**
     * @return mixed
     */
    public function getAppointmentCountAttribute()
    {
        $vcardQuery = Vcard::where('tenant_id', auth()->user()->tenant_id);

        if (isOrganisationUser()) {
            $vcardQuery->whereHas('assignedUser', function ($q) {
                $q->where('user_id', getLogInUserId());
            });
        }

        $vcardIds = $vcardQuery->select('id');

        $today = Carbon::now()->format('Y-m-d');

        return ScheduleAppointment::whereIn('vcard_id', $vcardIds)->whereDate('date', $today)->count();
    }

    /**
     * @return mixed
     */
    public function usersData($input)
    {
        if (isset($input['day'])) {
            $data = User::whereRaw('Date(created_at) = CURDATE()')->orderBy('created_at', 'DESC')
                ->paginate(5);
            return $data;
        }

        if (isset($input['week'])) {
            $now = Carbon::now();
            $weekStartDate = $now->startOfWeek()->format('Y-m-d H:i');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d H:i');
            $data = User::whereBetween('created_at', [$weekStartDate, $weekEndDate])
                ->orderBy('created_at', 'DESC')
                ->paginate(5);

            return $data;
        }

        if (isset($input['month'])) {
            $data = User::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->orderBy('created_at', 'DESC')
                ->paginate(5);

            return $data;
        }
    }

    public function planChartData(): array
    {
        $plans = Plan::withCount([
            'subscriptions' => function ($q) {
                $q->where('status', true);
            },
        ])->pluck('subscriptions_count', 'name')->toArray();

        $totalSubsPlan = array_sum($plans);
        $data = [];
        foreach ($plans as $name => $planCount) {
            $data['labels'][] = $name;
            $data['breakDown'][] = number_format($planCount * 100 / $totalSubsPlan, 2);
        }

        return $data;
    }


    public function incomeChartData($input)
    {
        if ($input) {

            $startDate = Carbon::parse($input['start_date']);
            $endDate = Carbon::parse($input['end_date']);
            $daysDiff = $startDate->diffInDays($endDate);
            $periodInterval = $daysDiff <= 31 ? '1 day' : '1 month';
            $groupFormat = $periodInterval === '1 day' ? 'Y-m-d' : 'Y-m';

            $manualPayment = Subscription::wherePaymentType('cash')
                ->where(function ($q) {
                    $q->where('status', Subscription::ACTIVE)
                        ->orWhere('status', Subscription::INACTIVE);
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($q) use ($groupFormat) {
                    return Carbon::parse($q->created_at)->format($groupFormat);
                });

            $transactions = Transaction::whereStatus(1)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($q) use ($groupFormat) {
                    return Carbon::parse($q->created_at)->format($groupFormat);
                });

            $labels = $dataset = $colors = $borderColors = [];

            $periods = CarbonPeriod::create($startDate, $periodInterval, $endDate);

            foreach ($periods as $key => $period) {

                if ($periodInterval === '1 day') {
                    $labels[] = $period->format('d M');
                    $groupKey = $period->format('Y-m-d');
                } else {
                    $labels[] = $period->isoFormat('MMM');
                    $groupKey = $period->format('Y-m');
                }

                $amounts = isset($manualPayment[$groupKey])
                    ? $manualPayment[$groupKey]->pluck('payable_amount')->toArray()
                    : [0];

                $dataset[] = removeCommaFromNumbers(number_format(array_sum($amounts), 2));
                $colors[] = getBGColors($key) . ')';
                $borderColors[] = getBGColors($key) . ', 0.75)';
            }

            //  Center single date ranges: Today / Yesterday
            if (count($labels) < 2) {

                array_unshift($labels, '', '');
                array_unshift($dataset, 0, 0);
                array_unshift($colors, 'rgba(0,0,0,0)');
                array_unshift($borderColors, 'rgba(0,0,0,0)');

                $labels[] = '';
                $labels[] = '';
                $dataset[] = 0;
                $dataset[] = 0;
                $colors[] = 'rgba(0,0,0,0)';
                $colors[] = 'rgba(0,0,0,0)';
                $borderColors[] = 'rgba(0,0,0,0)';
                $borderColors[] = 'rgba(0,0,0,0)';
            }

            return [
                'labels' => $labels,
                'breakDown' => [
                    [
                        'label' => __('messages.common.total_amount'),
                        'data' => $dataset,
                        'backgroundColor' => $colors,
                        'borderColor' => $borderColors,
                        'lineTension' => 0.5,
                        'radius' => 4,
                    ],
                ],
            ];
        }
    }
}
