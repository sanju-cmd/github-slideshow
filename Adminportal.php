<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Category;
use Session;
class Adminportal extends Controller
{
    function default()
    {
        return view('admin.adminlogin');
    }
    function adminpost(Request $req )
    {
        $email=$req->get('email');
        $password=sha1($req->get('password'));
        $data=Admin::where(['email'=>$email,'password'=>$password])->get();
        if(count($data)>0)
        {
            Session::put('sid',$email);
            return redirect('/adminpanel/dashboard');
        }
        else
        {
            Session::flash('errMsg','Email or Password is Invalid');
            return redirect('/adminpanel');
        }
    }
    function dashboard()
    {
        return view('admin.dashboard');
    }
    function category()
    {
        $catdata=Category::get();
        return view('admin.category',['catdata'=>$catdata]);
    }
    function logout()
    {
        Session::flush();
        return redirect('/adminpanel');
    }
    function changepassword()
    {
       return view('admin.changepass'); 
    }
    function postchangepassword(Request $req)
    {
        //return $req;
        $op=$req->get('op');
        $np=$req->get('np');
        $cp=$req->get('cp');
        $sid=session('sid');
        if($np==$cp)
        {
            $data=Admin::where('email',$sid)->first();
            if($data->password==sha1($op))
            {
                if($op==$np)
                {
                    Session::flash('errMsg','New password is not sameas old password');
            return redirect('/adminpanel/changepassword');
                }
                else
                {
                    $update=Admin::find($data->id);
                    $update->password=sha1($np);
                    if($update->save())
                    {
                        Session::flash('succMsg',' Password Changed Successfully');
            return redirect('/adminpanel/changepassword');
                    }
                    else
                    {
                        Session::flash('errMsg','Password not changed');
            return redirect('/adminpanel/changepassword');
                    }
                }
            }
            else
            {
                Session::flash('errMsg','New Password not matched with cp');
            return redirect('/adminpanel/changepassword');
            }
        }
        else
        {
            Session::flash('errMsg',' New password and Old password not matched');
            return redirect('/adminpanel/changepassword');
        }
    }
    function addcategory()
    {
        return view('admin.addcategory');
    }
    function postaddcategory(Request $req)
    {
        //return $req;
        $cname=$req->get('cname');
        $description=$req->get('description');
        $file=$req->file('file');
        $dest=public_path('/uploads');
        $fname="Image-".rand()."-".time().".".$file->extension();
        if($file->move($dest,$fname))
        {
            $cat=new Category();
            $cat->cname=$cname;
            $cat->description=$description;
            $cat->image=$fname;
            if($cat->save())
            {
                Session::flash('succMsg','Category Added');
            Return redirect('/adminpanel/category');
            }
            else
            {
                $path=public_path()."/uploads/".$fname;
                unlink($path);
                Session::flash('errMsg','Category Not Added');
            Return redirect('/adminpanel/addcategory');
            }
        }
        else
        {
            Session::flash('errMsg','Uploading Error');
            Return redirect('/adminpanel/addcategory');
        }
    }
    function delcategory($id)
    {
        echo $id;
        $cdata=Category::where('id',$id)->first();
        $imgpath=public_path().'/uploads/'.$cdata->image;
       $cat=Category::find($id);
       if($cat->delete())
       {
           unlink($imgpath);
           Session::flash('succMsg','Category Deleted');
           return redirect('adminpanel/category');
       }
       else
       {
        Session::flash('errMsg','Category Not Deleted');
        return redirect('adminpanel/category');
        }
    }
    function editcategory($id)
    {
        $cdata=Category::where('id',$id)->first();
        return view('admin.editcategory',['cdata'=>$cdata]);
    }
    function posteditcategory(Request $req)
    {
        $cname=$req->get('cname');
        $description=$req->get('description');
        $file=$req->file('file');
        $cid=$req->get('hid');
        if(empty($file))
        {
            //edit category without image
            $cat=Category::find($cid);
            $cat->cname=$cname;
            $cat->description=$description;
            if($cat->save())
            {
                Session::flash('succMsg','Category Updated');
                return redirect('adminpanel/category');
            }
            else
            {
                Session::flash('errMsg','Category Not Updated');
                return redirect('adminpanel/category');
            }
        }
        else
        {
            //category update with Image
            $fname='Image-'.rand()."-".time()."-".$file->extension();
            if($file->move('public/uploads',$fname))
            {
                $cat=Category::find($cid);
                //return $cat;
                //exit;
                $imgpath=public_path().'/uploads/'.$cat->image;
            $cat->cname=$cname;
            $cat->description=$description;
            $cat->image=$fname;
            if($cat->save())
            {
                unlink($imgpath);
                Session::flash('succMsg','Category Updated');
                return redirect('adminpanel/category');
            }
            else
            {
                Session::flash('errMsg','Category Not Updated');
                return redirect('adminpanel/category');
            }
            }
            else
            {
                Session::flash('errMsg','Uploading Error');
                return redirect('adminpanel/category');
            }
        }
    }
}
