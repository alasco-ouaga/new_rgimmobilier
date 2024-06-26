<?php

namespace App\Http\Controllers\programming;

use App\Http\Controllers\Api\propertiesController;
use App\Http\Controllers\Controller;
use App\Mail\agentMail;
use App\Mail\custumerMail;
use Illuminate\Http\Request;
use App\Models\ProgramingSearch;
use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Models\Locality;
use Botble\Blog\Models\Category as ModelsCategory;
use Botble\Location\Models\City;
use Botble\RealEstate\Models\Property;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use LDAP\Result;

use function PHPSTORM_META\type;

class programingController extends Controller
{
    public function sendMailToCustumer($custumer_id){
        $message="go";
        // $custumer = Account::find($custumer_id);
        // $message = "Cher(e)".$custumer->first_name." ".$custumer->last_name;
        // $message = $message." "."Nous vous remercions chaleureusement pour avoir choisi [Rgi.imobilier] pour vos besoin de maison";
        // $message = $message." "."Nous sommes ravis de vous servir et de vous offrir une expérience exceptionnelle. Nous confirmons que nous avons bien reçu votre recherche programmée et que celle-ci est actuellement en cours de traitement.";
        return $message;
    }

    public function agentEmail(){
        $emails = Account::where("status",true)->pluck('email');
        return $emails;
    }

    public function projectProgramingSession(){
        $data = null;
        $category = null;
        $city = null;
        if(Cache::has('programing_data')){
            $data = Cache::get("programing_data");
            
            if(isset($data['category_id'])){
                if($data['category_id'] != null){
                    $category = Category::find($data["category_id" ]);
                }
            }

            if(isset($data['city_id'])){
                if($data['city_id'] != null){
                    $city = City::find($data["city_id"])->name;
                }
            }
        }
        return ["data"=>$data,"category"=>$category->name ,"city"=>$city];
    }

    public function propertyProgramingSession(){

        $data = null;
        $localities = null;
        $city_name = null;
        $city_id = null;
        $category_id = null;
        $category_name = null;
        if(Cache::has('programing_data')){
            $data = Cache::get("programing_data");
            if(isset($data['category_id'])){
                if($data['category_id'] != null){
                    $category = Category::where("id",$data["category_id" ])->first();
                }
                $category_name = $category->name;
                $category_id = $category->id;

            }
            if(isset($data['city_id'])){
                if($data['city_id'] != null){
                    $city = City::find($data["city_id" ]);
                }
                $city_name = $city->name;
                $city_id= $city->id;
                if($city_id != null){
                    $localities = Locality::where('city_id',$city_id)->get();
                }
            }

        }
        return ["data"=>$data,"category"=>$category_name ,"category_id"=>$category_id,"city"=>$city_name ,"city_id"=>$city_id , "localities"=>$localities];
    }



    public function propertyProgramingSave(Request $request) {

        $category = null;
        $success = false;
        $locality_name = "";

        if($request->account_id != null){
            $custumer = Account::find($request->account_id);
            if($custumer == null){
                return ["success"=>false,"error"=>"custumer not found"];
            }
        }

        if($request->locality_id != null){
            $locality = Locality::find($request->locality);
            if($locality != null){
                 $locality_name = $locality->name;
            }
         }

        if(($request->type == "rent" || $request->type == "sale") && ($request->account_id != null) ){
            $programming = [
                "account_id"            => $request->account_id,
                "locality"              => $locality_name,
                "locality_id"           => $request->locality_id,
                "type"                  => $request->type,
                "city"                  => $request->city,
                "city_id"               => $request->city_id,
                "category"              => $request->category,
                "category_id"           => $request->category_id,
                "min_price"             => $request->min_price,
                "max_price"             => $request->max_price,
                "number_bedroom"        => $request->bedroom,
                "number_bathroom"       => $request->bathroom,
                "number_floor"          => $request->floor,
                "found"                 => false,
                "nb_found"              => 0,
                "created_at"            => now(),
                "updated_at"            => now()
            ];
    
            $account = Account::find($request->account_id)->first();

            if(ProgramingSearch::insert($programming))
            {  
                try {

                    $url = 'https://www.google.com';
                    $headers = @get_headers($url);
                    if ($headers && strpos($headers[0], '200')) {
                        $response = Mail::to($account->email)->send(new custumerMail($account));
                        if(!$response){
                            $error = "Erreur de messagerie";
                            $success = true;
                        }
                    }
                    
                    $emails = $this->agentEmail();
                    $url = 'https://www.google.com';
                    $headers = @get_headers($url);
                    if ($headers && strpos($headers[0], '200')) {
                        foreach($emails as $email){
                            $account = Account::where("email",$email)->first();
                            if($account != null){
                                if($request->category_id != null){
                                    $category = Category::where("id",$request->category_id)->first();
                                }
                                $response = Mail::to($email)->send(new agentMail($programming ,$category ,$account));
                            }
                          
                           if(!$response){
                              break;
                           }
                        }

                        $error = "No error noted";
                        $success = true;
                    }
                    Cache::forget("programing_data");
                } catch (\Exception $e) {
                    $success = false;
                    $error = "erreur : une ereur est survenu lors de l'envoie de message , l'utilisateur ne sera pas notifier";
                }
            
                return ["success"=> $success , "error"=>$error] ;
            }
            else{
                $success = false;
                $error = "Access denied";
                return ["success"=> $success , "error"=>$error] ;
            }
        }
        else{
            $error = "Invalid data" ;
            return ["success"=> $success ,"error"=>$error] ;
        }
    }

    public function projectProgramingSave(Request $request){
        return $request;
    }
   
    public function programing_search_click() {
        $data = Cache::get("programing_data");
        return $data;
    }

    public function get_data(Request $request) {
        dd("go");
        return response()->json($request->user_id);
    }

}
