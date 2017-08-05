<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Book;
class Author extends Model
{
    //

    protected $fillable = ['name'];


    public function book()
    {
    	return $this->hasMany('App\Book');
    }
    public static function boot()
    {
    	parent::boot();

    	self::deleting(function($author){
    		//mengecek apakah penulis masih punya buku
    		if ($author->book->count()>0){
    			//menyiapkan pesan eror
    			$html = 'Penulis tidak bisa dihapus karena masih memiliki buku : ';
    			$html .='<ul>';
    			foreach ($author->book as $book) {
    				$html .= "<li>$book->title</li>";
    			}
    			$html .='</ul>';

    			Session::flash("flash_notification", [
    				"level"=>"danger",
    				"message"=>$html ]);

    			//membatalkan proses penghapusan
    			return false;
    		}
    	});
    }
    
}
