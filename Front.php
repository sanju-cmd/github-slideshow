<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\product;
use App\Models\contact;
use App\Models\frontuser;
use Session;
use Illuminate\Http\Request;

class Front extends Controller
{
    function default()
    {
        $cdata=Category::get();
        $prodata=product::orderBy('id','desc')->limit(3)->get();
        return view('front.main',['catdata'=>$cdata,'prodata'=>$prodata]);
    }
    function contactus()
    {
        $cdata=Category::get();
        return view('front.contact',['catdata'=>$cdata]);
    }
    function postcontact(Request $req)
    {

        $contact=new contact();
        $contact->name=$req->name;
        $contact->email=$req->email;
        $contact->subject=$req->subject;
        $contact->message=$req->message;
        if($contact->save())
        {
            Session::flash('succMsg',"Contact Send");
            return redirect('/contact-us');
        }
        else
        {
            Session::flash('errMsg',"Contact NotSend");
            return redirect('/contact-us');
        }
    }
    function register(Request $req)
    {
        $user=new frontuser();
        $user->name=$req->name;
        $user->email=$req->email;
        $user->password=sha1($req->password);
        if($user->save())
        {
            Session::flash('succMsg',"User Registered");
            return redirect('/login');
        }
        else
        {
            Session::flash('errMsg',"User Not Registered");
            return redirect('/login');
        }
    }
    function postlogin(Request $req)
    {
        $password=sha1($req->password);
        $email=$req->email;
        $data=frontuser::where(['email'=>$email,'password'=>$password])->get();
        if(count($data)>0)
        {
            Session::put('uid',$email);
            return redirect('/');
        }
        else
        {
            Session::flash('errMsg',"Email Or Password Is Invalid");
            return redirect('/login');  
        }
    }
    function login()
    {
        $cdata=Category::get();
        return view('front.login',['catdata'=>$cdata]);
    }
    function logout()
    {
        Session::flush();
        return redirect('/');
    }
    function cart()
    {
        $cdata=Category::get();
        return view('front.cart',['catdata'=>$cdata]);
    }
    function categoryproduct($id)
    {
        $cdata=Category::get();
        $cname=Category::where('id',$id)->first();
        $prodata=product::where('cid',$id)->get();
        return view('front.categoryproduct',['catdata'=>$cdata,'catname'=>$cname->cname,'prodata'=>$prodata]);
    }
    function addcart($id)
    {
        $product=product::find($id);
        $cart=session('cart');
        //dd($cart);
        //exit;
        if(!$cart)//empty cart
        {
            $cart=[
            $id=>["pname"=>$product->pname,
            "price"=>$product->price,
            "quantity"=>1,
            "image"=>$product->image,

            ]
            ];
            Session::put('cart',$cart);
            Session::flash('msg',"Add Cart Successfully");
            return redirect('/');
        }
        if(isset($cart[$id]))
        {
            $cart[$id]['quantity']++;
            Session::put('cart',$cart);
            Session::flash('msg',"Add Cart Successfully");
            return redirect('/');
        }
        $cart[$id]=[
            "pname"=>$product->pname,
            "price"=>$product->price,
            "quantity"=>1,
            "image"=>$product->image
        ];
        Session::put('cart',$cart);
            Session::flash('msg',"Add Cart Successfully");
            return redirect('/');
    }
}
