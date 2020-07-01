<?php

declare(strict_types=1);

namespace Francken\Study\BooksSale\Http;

use Francken\Association\LegacyMember;
use Francken\Study\BooksSale\Book;
use Francken\Study\BooksSale\Http\Requests\AdminBookRequest;
use Francken\Study\BooksSale\Http\Requests\AdminBookSearchRequest;
use Illuminate\Database\Eloquent\Builder;

final class AdminBooksController
{
    public function index(AdminBookSearchRequest $request)
    {
        $books = Book::query()
            ->search($request)
            ->when($request->selected('available'), function (Builder $query) : void {
                $query->available();
            })
            ->when($request->selected('paid-off'), function (Builder $query) : void {
                $query->paidOff();
            })
            ->when($request->selected('sold'), function (Builder $query) : void {
                $query->sold();
            })
            ->with(['seller', 'buyer'])
            ->orderBy('id', 'desc')
            ->paginate(30)
            ->appends($request->except('page'));

        return view('admin.study.books.index', [
            'request' => $request,
            'books' => $books,
            'available_books' => Book::available()->count(),
            'sold_books' => Book::sold()->count(),
            'paid_off_books' => Book::paidOff()->count(),
            'all_books' => Book::count(),
            'members' => LegacyMember::autocomplete(),
            'breadcrumbs' => [
                ['url' => action([self::class, 'index']), 'text' => 'Books'],
            ]
        ]);
    }

    public function show(Book $book)
    {
        return view('admin.study.books.show', [
            'book' => $book,
            'members' => LegacyMember::autocomplete(),
            'breadcrumbs' => [
                ['url' => action([self::class, 'index']), 'text' => 'Books'],
                ['url' => action([self::class, 'show'], ['book' => $book]), 'text' => $book->title],
            ]
        ]);
    }

    public function create()
    {
        return view('admin.study.books.create', [
            'book' => new Book(),
            'members' => LegacyMember::autocomplete(),
            'breadcrumbs' => [
                ['url' => action([self::class, 'index']), 'text' => 'Books'],
                ['url' => action([self::class, 'create']), 'text' => 'Add'],
            ]
        ]);
    }

    public function store(AdminBookRequest $request)
    {
        $book = [
            "naam" => $request->title(),
            "editie" => $request->edition(),
            'auteur' => $request->author(),
            'beschrijving' => $request->description(),
            'isbn' => $request->isbn(),
            'prijs' => $request->price(),

            'verkoperid' => $request->sellerId(),
            'koperid' => $request->buyerId(),

            'inkoopdatum' => $request->purchaseDate(),
            'verkoopdatum' => $request->saleDate(),

            "verkocht" => $request->hasBeenSold(),
            "afgerekend" => $request->hasBeenPaidOff(),
        ];

        $book = Book::create($book);

        return redirect()->action([self::class, 'show'], ['book' => $book]);
    }

    public function update(AdminBookRequest $request, Book $book)
    {
        $book->update([
            "naam" => $request->title(),
            "editie" => $request->edition(),
            'auteur' => $request->author(),
            'beschrijving' => $request->description(),
            'isbn' => $request->isbn(),
            'prijs' => $request->price(),

            'verkoperid' => $request->sellerId(),
            'koperid' => $request->buyerId(),

            'inkoopdatum' => $request->purchaseDate(),
            'verkoopdatum' => $request->saleDate(),

            "verkocht" => $request->hasBeenSold(),
            "afgerekend" => $request->hasBeenPaidOff(),
        ]);

        return redirect()->action([self::class, 'show'], ['book' => $book]);
    }

    public function remove(Book $book)
    {
        if ($book->buyer !== null) {
            return redirect()->action([self::class, 'show'], ['book' => $book])->with([
                'error' => "You are not allowed to remove a book that has been sold"
            ]);
        }

        $book->delete();

        return redirect()->action([self::class, 'index']);
    }

    public function print(Book $book)
    {
        return view('admin.study.books.print', [
            'book' => $book,
            'members' => LegacyMember::autocomplete(),
            'breadcrumbs' => [
                ['url' => action([self::class, 'index']), 'text' => 'Books'],
                ['url' => action([self::class, 'show'], ['book' => $book]), 'text' => $book->title],
            ]
        ]);
    }
}
