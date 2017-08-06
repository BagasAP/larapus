<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Html\Builder;
use Yajra\Datatables\Datatables;
use App\Book;
use Session;
use Illuminate\Support\Facades\File;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Requests\StoreBookRequest;


use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\BorrowLog;
use App\Exceptions\BookException;
class BooksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Builder $htmlBuilder)
    {
        if ($request->ajax())
        {
            $books = Book::with('author');
            return Datatables::of($books)
            ->addColumn('action', function($book){
                return view('datatable._action',[
                    'model' => $book,
                    'form_url' => route('books.destroy', $book->id),
                    'edit_url' => route('books.edit', $book->id),
                    'confirm_message' => 'Yakin mau menghapus'.$book->title.'?']);
            
            })->make(true);
        }

        $html = $htmlBuilder
        ->addColumn(['data' => 'title','name'=>'title','title'=>'Judul'])
        ->addColumn(['data' => 'amount','name'=>'amount','title'=>'Jumlah'])
        ->addColumn(['data' => 'author.name','name'=>'author.name','title'=>'Penulis'])
        ->addColumn(['data' => 'action', 'name'=>'action','title'=>'','orderable'=>false, 'searcable'=>false]);

        return view('books.index')->with(compact('html'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('books.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookRequest $request)
    {
        //
   

        $book = Book::create($request->except('cover'));
        //isi field cover jika ada cover yang di upload
            if ($request->hasFile('cover')) {
                $uploaded_cover = $request->file('cover');

                $extension = $uploaded_cover->getClientOriginalExtension();

                $filename = md5(time()).'.'.$extension;

                $destinationPath = public_path(). DIRECTORY_SEPARATOR . 'img';
                $uploaded_cover->move($destinationPath, $filename);

                $book->cover = $filename;
                $book->save();
            }

         Session::flash("flash_notification",[
            "level"=>"success",
            "message"=>"Berhasil menyimpan $book->title"
            ]);

         return redirect()->route('books.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $book = Book::find($id);
        return view('books.edit')->with(compact('book'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookRequest $request, $id)
    // public function update(Request $request, $id)
    {
        //
        // $this->validate($request,[
        //     'title' => 'required|unique:books,title,'.$id,
        //     'author_id' => 'required|exists:authors,id',
        //     'amount' => 'required|numeric',
        //     'cover' => 'image|max:2048']);

        $book = Book::find($id);
        if (!$book->update($request->all()));
        if ($request->hasFile('cover')) {
            // mengambil cover yang diupload berikut ekstensinya
            $filename = null;
            $uploaded_cover = $request->file('cover');
            $extension = $uploaded_cover->getClientOriginalExtension();

            // membuat nama file random dengan extension
            $filename = md5(time()) . '.' . $extension;
            $destinationPath = public_path() . DIRECTORY_SEPARATOR . 'img';

            // memindahkan file ke folder public/img
            $uploaded_cover->move($destinationPath, $filename);

            // hapus cover lama jika ada
            if ($book->cover) {
                 $old_cover = $book->cover;
                 $filepath = public_path() . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $book->cover;
                 try{
                    File::delete($filepath);
                 }
                    catch(FileNotFoundException $e){
                    // file sudah dihapus/tidak ada
                 }
             }
             // ganti field dengan cover yang baru
             $book->cover = $filename;
             $book->save();
        }

        Session::flash("flash_notification", [
            "level"=>"succes",
            "message"=>"Berhasil menyimpan $book->title"
            ]);
        return redirect()->route('books.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
               
    }


}
