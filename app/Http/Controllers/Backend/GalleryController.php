<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\Contact;

class GalleryController extends Controller
{
    public function AllGallery()
    {
        $gallery = Gallery::latest()->get();
        return view('backend.gallery.all_gallery', compact('gallery'));
    }

    public function AddGallery()
    {
        return view('backend.gallery.add_gallery');
    }

    public function StoreGallery(Request $request)
    {
        $images = $request->file('photo_name');
        $manager = new ImageManager(new Driver());

        foreach ($images as $img) {
            $name_gen = hexdec(uniqid()) . '.' . $img->getClientOriginalExtension();
            $imagePath = 'upload/gallery/' . $name_gen;

            // Resize and save image
            $manager->read($img)->resize(550, 550)->save(public_path($imagePath));

            // Save to DB
            Gallery::insert([
                'photo_name' => $imagePath,
                'created_at' => Carbon::now(),
            ]);
        }

        $notification = [
            'message' => 'Gallery Inserted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.gallery')->with($notification);
    }

    public function EditGallery($id)
{
    $gallery = Gallery::find($id);
    return view('backend.gallery.edit_gallery', compact('gallery'));
}

// -------------------------------

public function UpdateGallery(Request $request)
{
    $gal_id = $request->id;
    $img = $request->file('photo_name');

    $manager = new ImageManager(new Driver());

    $name_gen = hexdec(uniqid()) . '.' . $img->getClientOriginalExtension();
    $save_path = 'upload/gallery/' . $name_gen;

    // Save resized image
    $manager->read($img)->resize(550, 550)->save(public_path($save_path));

    // Delete old image
    $old = Gallery::findOrFail($gal_id);
    if (file_exists(public_path($old->photo_name))) {
        unlink(public_path($old->photo_name));
    }

    // Update in DB
    $old->update([
        'photo_name' => $save_path,
    ]);

    $notification = [
        'message' => 'Gallery Updated Successfully',
        'alert-type' => 'success',
    ];

    return redirect()->route('all.gallery')->with($notification);
}

// -------------------------------

public function DeleteGallery($id)
{
    $item = Gallery::findOrFail($id);

    // Delete image file
    $imagePath = public_path($item->photo_name);
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Delete DB record
    $item->delete();

    $notification = [
        'message' => 'Gallery Image Deleted Successfully',
        'alert-type' => 'success',
    ];

    return redirect()->back()->with($notification);
}

 public function DeleteGalleryMultiple(Request $request){

        $selectedItems = $request->input('selectedItem', []);

        foreach ($selectedItems as $itemId) {
           $item = Gallery::find($itemId);
           $img = $item->photo_name;
           unlink($img);
           $item->delete();
        }

        $notification = array(
            'message' => 'Selected Image Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

     }// End Method 


     public function ShowGallery(){
        $gallery = Gallery::latest()->get();
        return view('frontend.gallery.show_gallery',compact('gallery'));
     }// End Method

      public function ContactUs(){

        return view('frontend.contact.contact_us');
     }// End Method


     public function StoreContactUs(Request $request){

        Contact::insert([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'created_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Your Message Send Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 

     }// End Method

     public function AdminContactMessage(){

        $contact = Contact::latest()->get();
        return view('backend.contact.contact_message',compact('contact'));

     }// End Method

}
