<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth as Auth;
use App\Models\Kategori;
use App\Models\Berita;
use Illuminate\Support\Facades\Hash as Hash;

class DashboardController extends Controller
{
    public function index(){
        $totalBerita = Berita::count();
        $totalKategori = Kategori::count();
        $totalUser = User::count();

        $latestBerita = Berita::with('kategori')->latest()->get()->take(5);
        return view('backend.content.dashboard',compact('totalBerita','totalKategori','totalUser','latestBerita'));
    }

    public function profile(){
        $id = Auth::guard('user')->user()->id;
        $user = User::findOrFail($id);
        return view('backend.content.profile',compact('user'));
    }

    public function resetPassword(){
        return view('backend.content.resetPassword');
    }

    public function prosesResetPassword(Request $request){
        $this->validate($request,[
           'old_password'=>'required',
           'new_password'=>'required|min:6',
           'c_new_password'=>'required_with:new_password|same:new_password|min:6',
        ]);

        $old_password = $request->old_password;
        $new_password = $request->new_password;

        $id =Auth::guard('user')->user()->id;
        $user = User::findOrFail($id);

        if (Hash::check($old_password, $user->password)){
            $user->password = bcrypt($new_password);

            try {
                $user->save();
                return redirect(route('dashboard.resetPassword'))->with('pesan',['success','Selamat anda berhasil merubah password']);
            }catch (\Exception $e){
                return redirect(route('dashboard.resetPassword'))->with('pesan',['danger','anda gagal merubah password']);
            }
        }else{
            return redirect(route('dashboard.resetPassword'))->with('pesan',['success','Password lama salah']);
        }
    }
}

