<?php

namespace App\Http\Livewire\Programing;

use App\Models\ProgramingSearch;
use Botble\Blog\Models\Category;
use Botble\RealEstate\Models\Category as ModelsCategory;
use Livewire\Component;

class FiltrePrograming extends Component
{
    public $quantity = 0;
    public $parametter = "";
    public $type="";
    public $programings;
    public $programingscopies;

    public function mount(){
        $this->programings = ProgramingSearch::orderBy('created_at', 'desc')->where("find",false)->take(5)->latest()->get();
        $this->programingscopies = $this->programings;
    }

    public function render()
    {

        if($this->quantity != 0){
            $this->programings = ProgramingSearch::orderBy('created_at', 'desc')->where("find",false)->take($this->quantity)->latest()->get();
            $this->programingscopies = $this->programings;
        }

        if($this->type != ""){
            $this->programings = $this->programingscopies;
            $programinglimited = $this->programings->filter(function ($programing) {
                return stripos(strtolower($programing->type_name), strtolower($this->type)) !== false;
            });
            $this->programings = $programinglimited;
        }

        if($this->parametter != ""){
            $programinglimited =  $this->programings->filter(function ($programing) {
                return (
                    stripos(strtolower($programing->category_name), strtolower($this->parametter)) !== false || 
                    stripos(strtolower($programing->city_name), strtolower($this->parametter)) !== false
                );
            });
            $programings = $programinglimited;
            if(count($programings) == 0){
                $programinglimited = $this->programings->filter(function ($programing) {
                    return (
                        ((($this->parametter) >= $programing->min_price) !== false && 
                        (($this->parametter) <= $programing->max_price) !== false )
                    );
                });
                $programings = $programinglimited;
            }
            $this->programings =  $programings;
            dd($this->programings);
        }

        return view('livewire.programing.filtre-programing');
    }
}
