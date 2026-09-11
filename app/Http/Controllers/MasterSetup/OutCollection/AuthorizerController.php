<?php

namespace App\Http\Controllers\MasterSetup\OutCollection;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Deposit;
use App\Models\Payment;
use App\Traits\HasAuditLog;
use App\Traits\HasInfoLogChannel;
use App\Traits\HasLogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class AuthorizerController extends Controller
{
    use HasInfoLogChannel, HasLogContext, HasAuditLog;

    protected $defaultErrorMessage = "Error submitting request: ";
    protected $defaultSentToAuthorStatus = "Sent to Author";
    protected $defaultSentBackStatus = "Sent Back";
    protected $defaultAuthorizedStatus = "Authorized";
    protected $module = 'Out Collection - Receipt Posting';
    protected $log;

    public function __construct()
    {
        $this->log = $this->getInfoLogChannel('out_collection');
    }

    public function sentToAuthor(Request $request)
    {
        $depositId = $request->data['id'];
        $payments = Payment::where('deposit_id', $depositId);
        $deposit = Deposit::findOrFail($depositId);

        $context = $this->getLogContext(request(), [
            'deposit_id'    => $depositId,
            'deposit_ids'   => $payments->pluck('id')->toArray(),
            'deposit_count' => $payments->count(),
            'target_status' => $this->defaultSentToAuthorStatus,
        ]);

        $this->log->info('Send to author initiated', $context);

        DB::beginTransaction();

        try {
            $payments->update(['status' => $this->defaultSentToAuthorStatus]);

            $deposit->update(['status' => $this->defaultSentToAuthorStatus]);

            DB::commit();

            $this->log->info('Send to author successful', $context);

            $this->auditLogs('STATUS UPDATED TO: SENT TO AUTHOR', $this->module, $payments->first());

            return redirect("/out-collection/$depositId/view-deposit")
                ->with(['success' => 'Receipt sent to author successfully']);
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->log->error('Send to author failed', [
                ...$context,
                'error' => $th->getMessage(),
                'trace' => app()->isProduction() ? null : $th->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => $this->defaultErrorMessage . $th->getMessage()]);
        }
    }

    public function authorize(Request $request)
    {
        $depositId = $request->data['id'];
        $payments = Payment::where('deposit_id', $depositId);
        $deposit = Deposit::findOrFail($depositId);
        $user = Auth::user();

        $context = $this->getLogContext(request(), [
            'deposit_id'        => $depositId,
            'target_status'     => $this->defaultAuthorizedStatus,
        ]);

        $this->log->info('Out collection authorization initiated', $context);

        if (!$user->can('out_collection.authorize')) {
            $this->log->warning('Unauthorized authorization attempt', $context);

            return redirect()->back()->withErrors([
                'authorization' => 'You are not authorized to perform this action'
            ]);
        }

        DB::beginTransaction();

        try {
            $updatedDeposit = $deposit->update(['status' => $this->defaultAuthorizedStatus]);

            $this->auditLogs('STATUS UPDATED TO: AUTHORIZED', $this->module, $deposit->first());

            $updatedPayment = $payments->update([
                'status' => $this->defaultAuthorizedStatus,
                'authorizerId' => $user->employee_id,
                'dateAuthor' => now()->format('Y-m-d'),
            ]);

            $this->auditLogs('STATUS UPDATED TO: AUTHORIZED', $this->module, $payments->first());

            DB::commit();

            $this->log->info('Out collection authorization successful', [
                ...$context,
                'updated_deposit'  => $updatedDeposit,
                'updated_payment' => $updatedPayment,
            ]);


            return redirect("/out-collection/$depositId/view-deposit")
                ->with('success', 'Receipt authorized successfully');
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->log->error('Out collection authorization failed', [
                ...$context,
                'error' => $th->getMessage(),
                'trace' => app()->isProduction() ? null : $th->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => $this->defaultErrorMessage . $th->getMessage()
            ]);
        }
    }

    public function sendBack(Request $request)
    {
        $isCheckedAll = $request->isCheckedAll;
        $user = Auth::user();
        $sentBack = 'STATUS UPDATED TO: SENT BACK';

        if ($isCheckedAll) {
            $validated = $request->validate([
                'items' => ['required', 'array'],
                'remarks' => ['required', 'string'],
            ]);
        } else {
            $validated = $request->validate([
                'items' => ['required', 'array'],
                'itemRemarks' => ['required', 'array']
            ]);
        }

        $context = $this->getLogContext($request, [
            'is_checked_all'      => $isCheckedAll,
            'target_status'       => $this->defaultSentBackStatus,
            'item_ids'            => collect($validated['items'])->pluck('id')->toArray(),
            'item_count'          => count($validated['items']),
            'out_collection_ids'  => collect($validated['items'])->pluck('out_collection_id')->unique()->toArray(),
            'remarks'             => $isCheckedAll ? ($validated['remarks'] ?? null) : null,
        ]);

        $this->log->info('Out Collection send back initiated', $context);

        if (!$user->can('out_collection.authorize')) {
            $this->log->warning('Unauthorized send back attempt', $context);

            return redirect()->back()->withErrors([
                'authorization' => 'You are not authorized to perform this action'
            ]);
        }

        DB::beginTransaction();

        try {

            if ($isCheckedAll) {
                foreach ($validated['items'] as $item) {
                    $payment = Payment::find($item['id']);
                    $payment->update([
                        'status' => $this->defaultSentBackStatus,
                        'remarks' => $validated['remarks'] ?? null
                    ]);
                    $this->auditLogs($sentBack, $this->module, $payment->first());
                }
            } else {
                foreach ($validated['itemRemarks'] as $key => $value) {
                    $payment = Payment::find($key);
                    $payment->update([
                        'status' => $this->defaultSentBackStatus,
                        'remarks' => $value ?? null
                    ]);
                    $this->auditLogs($sentBack, $this->module, $payment->first());
                }
            }

            foreach ($validated['items'] as $item) {
                $deposit = Deposit::find($item['deposit_id']);
                $deposit->update(['status' => $this->defaultSentBackStatus]);
                $this->auditLogs($sentBack, $this->module, $deposit);
            }

            DB::commit();

            $this->log->info('Out Collection send back successful', $context);

            return back()->with(['success' => 'Request Sent Back Successfully.']);
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->log->error('Out Collection send back failed', [
                ...$context,
                'error' => $th->getMessage(),
                'trace' => app()->isProduction() ? null : $th->getTraceAsString(),
            ]);

            return back()->withErrors([
                'errors' => $this->defaultErrorMessage . $th->getMessage()
            ]);
        }
    }
}
