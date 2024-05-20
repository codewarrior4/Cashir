<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function getTodayTransactions()
    {
        $transactions = Transaction::whereDate('created_at', Carbon::today())
        ->get();
        return response()->json($transactions);
    }

    public function getWeeklyTransactions()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $transactions = Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
        return response()->json($transactions);
    }

    public function getMonthlyTransactions()
    {
        $transactions = Transaction::whereMonth('created_at', Carbon::now()->month)->get();
        return response()->json($transactions);
    }

    public function getYearlyTransactions()
    {
        $transactions = Transaction::whereYear('created_at', Carbon::now()->year)->get();
        return response()->json($transactions);
    }
}
