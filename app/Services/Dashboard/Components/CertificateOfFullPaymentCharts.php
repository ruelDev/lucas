<?php

namespace App\Services\Dashboard\Components;

use App\Models\AuditLog;
use App\Models\LoanDetailsFinnone;
use App\Models\LoanDetailsNewgenBfc;
use App\Models\LoanDetailsNewgenBmi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CertificateOfFullPaymentCharts
{
    public function prepareCertificateOfFullPaymentChartsData()
    {

        $baseQuery = AuditLog::query();
        $logQuery = $baseQuery->where('module', 'CERTIFICATE OF FULL PAYMENT');
        
        $totalTopUserCFPCount =  $this->getTotalTopUserCFPCount($logQuery);
        $totalTopCFPGeneratedCount =  $this->getTotalTopCFPGeneratedCount($logQuery);
        $totalGenerationLogs =  $this->getGenerationLogs($logQuery);
        $totalListCFPGeneratedCount =  $this->getListCFPGeneratedCount($logQuery);

        return [
            'totalTopUserCFPCount' => $totalTopUserCFPCount,
            'totalTopCFPGeneratedCount' => $totalTopCFPGeneratedCount,
            'totalGenerationLogs' => $totalGenerationLogs,
            'totalListCFPGeneratedCount' => $totalListCFPGeneratedCount
        ];
    }

    private function getTotalTopUserCFPCount($logQuery)
    {

        return (clone $logQuery)
            ->with('user')
            ->selectRaw("
            user_id,
            COUNT(*) as total,
            SUBSTRING_INDEX(MAX(event), ' ', -1) as agreement,
            MAX(created_at) as last_activity
        ")
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                $user = $item->user;
                return [
                    'name' => trim($user->fname . " " . ($user->mname ?? '') . " " . $user->lname),
                    'count' => $item->total,
                    'agreement' => $item->agreement,
                    'last_activity' => $item->last_activity
                ];
            });
    }
    private function getTotalTopCFPGeneratedCount($logQuery)
    {
        return (clone $logQuery)
            ->with('user')
            ->selectRaw("
            COUNT(*) as total
  
        ")
            ->get()
            ->map(function ($item) {
                return [
                    'count' => $item->total,

                ];
            });
    }

    private function getListCFPGeneratedCount($logQuery)
    {
        $logs = (clone $logQuery)
            ->with('user')
            ->orderBy('created_at', 'DESC')
            ->get();

        $agreements = $this->getAgreementNumber($logs);

        [$config, $allCompanies, $grouped] = $this->getConfig();

        $grouped = $this->getBulkLoans($agreements, $allCompanies, $config, $grouped);

        $loanMap = array_merge(
            $this->getNewgenAccounts($allCompanies, $config, $grouped),
            $this->getFinnoneAccounts($grouped)
        );
        // Map results
        return $logs->map(function ($item) use ($loanMap) {
            $agreement = \Illuminate\Support\Str::afterLast($item->event, ' ');
            $user = $item->user;

            return [
                'name' => $loanMap[$agreement]
                    ?? trim($user->fname . " " . ($user->mname ?? '') . " " . $user->lname),
                'count' => 1,
                'agreement' => $agreement,
                'last_activity' => $item->created_at
            ];
        });
    }

    private function getAgreementNumber($logs)
    {
        // STEP 1: Extract agreements
        return $logs->map(fn($item) => \Illuminate\Support\Str::afterLast($item->event, ' '))
            ->filter()
            ->unique();
    }

    private function getConfig()
    {
        // CONFIG
        $config = [
            'BMI' => [
                'prefix' => 'TOM',
                'model' => LoanDetailsNewgenBmi::class,
                'relationship' => ['customersdetails']
            ],
            'BFC' => [
                'prefix' => 'TOB',
                'model' => LoanDetailsNewgenBfc::class,
                'relationship' => ['customersdetails']
            ]
        ];

        // IMPORTANT: always allow full lookup so cross-company resolution works
        $allCompanies = ['BMI', 'BFC'];

        // STEP 2: Group agreements
        $grouped = [
            'BMI' => [],
            'BFC' => [],
            'FINNONE' => []
        ];

        return [
            $config,
            $allCompanies,
            $grouped
        ];
    }

    private function getBulkLoans($agreements, $allCompanies, $config, $grouped)
    {
        foreach ($agreements as $agreement) {
            $matched = false;

            foreach ($allCompanies as $comp) {
                if (str_starts_with($agreement, $config[$comp]['prefix'])) {
                    $grouped[$comp][] = $agreement;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $grouped['FINNONE'][] = $agreement;
            }
        }

        // Return both loanMap (empty placeholder) AND the populated $grouped
        return $grouped;
        // STEP 3: Bulk fetch loans
    }
    private function getNewgenAccounts($allCompanies, $config, $grouped)
    {
        // BMI / BFC lookup (NO restriction here)
        $loanMap = [];
        foreach ($allCompanies as $comp) {
            if (!empty($grouped[$comp])) {
                $conf = $config[$comp];
                $model = $conf['model'];

                $loans = $model::with($conf['relationship'])
                    ->whereIn('LOAN_NO', $grouped[$comp])
                    ->where('REC_STATUS', 'C')
                    ->get();

                foreach ($loans as $loan) {
                    $loanMap[$loan->LOAN_NO] = $loan->customersdetails->customer_name ?? 'N/A';
                }
            }
        }
        return $loanMap;
    }
    private function getFinnoneAccounts($grouped)
    {
        // FINNONE
        $loanMap = [];
        if (!empty($grouped['FINNONE'])) {
            $loans = LoanDetailsFinnone::with([
                'customerdetailsfinnone'
            ])
                ->whereIn('agreementno', $grouped['FINNONE'])
                ->where('status', 'C')
                ->get();

            foreach ($loans as $loan) {
                $loanMap[$loan->agreementno] = $loan->customerdetailsfinnone->customername ?? 'N/A';
            }
        }
        return $loanMap;
    }
    private function getGenerationLogs($logQuery)
    {
        return (clone $logQuery)
            ->select(
                DB::raw('COUNT(event) AS count'),
                DB::raw('DATE(created_at) AS last_activity')
            )
            ->groupBy('last_activity')
            ->get()
            ->map(function ($item) {
                return [
                    'count' => $item->count,
                    'date' => $item->last_activity
                ];
            });
    }
}
