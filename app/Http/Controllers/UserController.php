<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Booking;

class UserController extends Controller
{
    public function Index(){
        return view('frontend.index');
    }// End Method 

    public function UserProfile(){

        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('frontend.dashboard.edit_profile',compact('profileData'));

    }// End Method 

    public function UserStore(Request $request){

        $id = Auth::user()->id;
        $data = User::find($id);
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        if($request->file('photo')){
            $file = $request->file('photo');
            @unlink(public_path('upload/user_images/'.$data->photo));
            $filename = date('YmdHi').$file->getClientOriginalName();  
            $file->move(public_path('upload/user_images'),$filename);
            $data['photo'] = $filename;

        }
        $data->save();

        $notification = array(
            'message' => 'User Profile Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }// End Method 


    public function UserLogout(Request $request){
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
            'message' => 'User Logout Successfully',
            'alert-type' => 'success'
        );

        return redirect('/login')->with($notification);
    }// End Method


    public function UserChangePassword(){

        return view('frontend.dashboard.user_change_password');

    }// End Method


    public function ChangePasswordStore(Request $request){

        // Validation 
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'
        ]);

        if(!Hash::check($request->old_password, auth::user()->password)){

            $notification = array(
                'message' => 'Old Password Does not Match!',
                'alert-type' => 'error'
            );
    
            return back()->with($notification);

        }

        /// Update The New Password 
        User::whereId(auth::user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        $notification = array(
            'message' => 'Password Change Successfully',
            'alert-type' => 'success'
        );

        return back()->with($notification); 

    }// End Method 

public function UserDashboard()
{
    $userId = Auth::id();

    $total = Booking::where('user_id', $userId)->count();
    $pending = Booking::where('user_id', $userId)->where('status', 'pending')->count();
    $complete = Booking::where('user_id', $userId)->where('status', 'complete')->count();

   $bookings = Booking::with('room') // not 'room'
    ->where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->get();


    return view('frontend.dashboard.user_dashboard', compact('total', 'pending', 'complete', 'bookings'));
}


public function BookingInvoice($id)
{
    $editData = Booking::with('room.type')->findOrFail($id);

    return view('frontend.dashboard.user_invoice', compact('editData'));
}



}
 