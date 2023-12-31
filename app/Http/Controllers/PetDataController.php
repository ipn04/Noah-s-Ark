<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;
use Illuminate\Support\Facades\Storage;

class PetDataController extends Controller
{
    public function showPetManagement()
    {
        $pets = Pet::all(); // Fetch all pets
        $petCount = Pet::count();
        $availpet = Pet::where('adoption_status', 'available')->count(); // Get the count of cats
        $dogCount = Pet::where('pet_type', 'dog')->count();

        return view('admin_contents.pet_management', ['pets' => $pets,'petCount' => $petCount, 'availpet' => $availpet,
        'dogCount' => $dogCount,]);
    }

    public function filterPets(Request $request)
    {
        
        $filteredPets = Pet::query();

        if ($request->category && $request->category !== 'All') {
            $filteredPets->where('pet_type', $request->category);
        }

        if ($request->availability && $request->availability !== 'All') {
            $filteredPets->where('adoption_status', $request->availability);
        }

        $filteredPets = $filteredPets->get();

        // Render the filtered pets view (e.g., pet-listing.blade.php) and return it as a response
        return view('initial_page.pet_lists', ['pets' => $filteredPets])->render();
    }


    public function showPublicPets()
    {
        $pets = Pet::all();

        return view('initial_page.pets', ['pets' => $pets]);
    }

    public function showPet($id)
    {
        $pet = Pet::find($id);
        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        return response()->json(['pet' => $pet]);
    }

    public function addPet(Request $request) {
        // Check the MIME type
        // dd($request->all());
        $validatedData = $request->validate([
            // Validation rules for other fields
            'pet_name' => 'required',
            'pet_type' => 'required',
            'breed' => 'required',
            'age' => 'required',
            'color' => 'required',
            'adoption_status' => 'required',
            'gender' => 'required',
            'vaccination_status' => 'required',
            'weight' => 'required', 
            'size' => 'required',
            'behaviour' => 'required',
            'description' => 'required',
            'dropzone_file' => 'required', // If this is a file upload
        ],
        [
            'dropzone_file.required' => 'The pet image is required. Please, try again',
            'dropzone_file.image' => 'The file must be an image.',
            'dropzone_file.mimes' => 'Allowed image formats are: jpeg, png, jpg, gif.',
            'dropzone_file.max' => 'Maximum file size allowed is 2MB.',]
        );

        if ($request->hasFile('dropzone_file')) {
            $image = $request->file('dropzone_file');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
        
            // Define the directory to store the image
            $directory = 'images'; // Update this directory as needed
        
            // Store the image in the 'images' directory within 'storage/app/public'
            $image->storeAs('public/' . $directory, $imageName);
        
            // Assign the image name to the 'dropzone_file' attribute in the validated data
            $validatedData['dropzone_file'] = $imageName;
        }
           
        $pet = Pet::create($validatedData);

        return redirect()->route('admin.pet.management')->with(['pet' => $pet, 'pet_added' => true]);
    }
    public function delete($id)
    {
        $pet = Pet::find($id);
        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        $imagePath = 'public/images/' . $pet->dropzone_file;
        if (Storage::disk('local')->exists($imagePath)) {
            Storage::disk('local')->delete($imagePath);
        }

        $pet->delete();

        session()->flash('pet_deleted', true);
        
        return response()->json(['message' => 'Pet deleted successfully']);
    }

    // search
    // public function search(Request $request){
    //     // Get the search value from the request
    //     $search = $request->input('search');
    //     $petCount = Pet::count();
    //     $catCount = Pet::where('pet_type', 'cat')->count();
    //     $dogCount = Pet::where('pet_type', 'dog')->count();
        
    //     // Search in the title and body columns from the posts table
        
    //     $pets = Pet::query()
    //         ->where('pet_name', 'LIKE', "%{$search}%")
    //         ->get();
    //         return response()->json(['pets' => $pets]);
    //     // Return the search view with the resluts compacted
    // }
}

