<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query=Subscriber::query();
        if($request->filled('search'))$query->where('email','like','%'.trim($request->search).'%');
        if($request->filled('from'))$query->whereDate('created_at','>=',$request->date('from'));
        if($request->filled('to'))$query->whereDate('created_at','<=',$request->date('to'));
        $subscribers=$query->latest()->paginate(20)->withQueryString();
        $subscriberStats=['total'=>Subscriber::count(),'today'=>Subscriber::whereDate('created_at',today())->count(),'month'=>Subscriber::whereYear('created_at',now()->year)->whereMonth('created_at',now()->month)->count()];
        return view('admin.subscribers.index',compact('subscribers','subscriberStats'));
    }

    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();
        return redirect()->back()->with('success', 'Subscriber removed successfully.');
    }
}
