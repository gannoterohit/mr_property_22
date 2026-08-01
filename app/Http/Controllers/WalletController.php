<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        if (\App\Models\Setting::get('wallet_enabled', '1') !== '1') {
            return redirect()->route('home')->with('error', 'Wallet system is currently inactive.');
        }
        $user=Auth::user();
        return view('account.wallet',compact('user'));
    }
    
    public function convertPoints(Request $request)
    {
        abort(404);
    }
}
