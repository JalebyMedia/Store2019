<?php

namespace App\Http\Controllers;

use App\Product;
use App\Category;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class ShopController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        $pagination = 9;
        $categories = Category::all();
        //$fouroverproducts = call_4over_curl();
        if (request()->category) {
            $products = Product::with('categories')->whereHas('categories', function ($query) {
                $query->where('slug', request()->category);
            });
            $categoryName = optional($categories->where('slug', request()->category)->first())->name;
        } else {
            $products = Product::where('featured', true);
            $categoryName = 'Featured';
        }
        if (request()->sort == 'low_high') {
            $products = $products->orderBy('price')->paginate($pagination);
        } elseif (request()->sort == 'high_low') {
            $products = $products->orderBy('price', 'desc')->paginate($pagination);
        } else {
            $products = $products->paginate($pagination);
        }
       
        return view('shop')->with([
            'products' => $products,
            'categories' => $categories,
            'categoryName' => $categoryName,
            //'categoryName' => $fouroverproducts,
        ]);
    }

    public function Getshipingquotes(Request $request) {
        $method='POST'; 
        $separator = '?';
      if ($request->type==1) {
        $json='{"product_info": {
            "product_uuid": "'.$request->pruuid.'",
            "runsize_uuid": "'.$request->ruuuid.'",
            "turnaround_uuid": "'.$request->opuuid.'",
            "colorspec_uuid":"'.$request->couuid.'",
            "option_uuids": [
                "'.$request->pruuid.'"
                 ] 
            },
        "shipping_address": {
            "zipcode": "'.$request->zip.'"
          }
      }';
    $uri=$request->endpoint;
    $result = call4overcurl($uri,$method,$separator,$json);
    return response()->json(['success'=>json_decode($result)]);
      }
      if ($request->type==2){
        $shipers = array();
        foreach (Cart::content() as $item){
            $json='{"product_info": {
                "product_uuid": "'.$item->options->produtid.'",
                "runsize_uuid": "'.$item->options->runsizeuuid.'",
                "turnaround_uuid": "'.$item->options->optionuuid.'",
                "colorspec_uuid":"'.$item->options->colorspecuuid.'",
                "option_uuids": [
                    "'.$item->options->produtid.'"
                     ] 
                },
            "shipping_address": {
                "address": "'.$request->address.'",
                "city": "'.$request->city.'",
                "state": "'.$request->province.'",
                "country": "US",
                "zipcode": "'.$request->postalcode.'"
              }
          }';
        $uri=$request->endpoint;
        $result = call4overcurl($uri,$method,$separator,$json);

        array_push($shipers,$result);
        }
        return response()->json(['success'=>$shipers]);
      }
    }
   
    public function sendtocart(Request $request) {
        $returnHTML = view('cart.index')->with('success_message', 'Item was added to your cart!')->render();
        dd($returnHTML);
        return response()->json(['success'=>true,'html'=>$returnHTML]);
    }
    public function call_4over_curl(Request $request) {
        $method='GET'; 
        $separator = '?';
        $json='';
        $uri=$request->endpoint;
        $result = call4overcurl($uri,$method,$separator,$json);
        return response()->json(['success'=>json_decode($result)]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $mightAlsoLike = Product::where('slug', '!=', $slug)->mightAlsoLike()->get();

        $stockLevel = getStockLevel($product->quantity);

        return view('product')->with([
            'product' => $product,
            'stockLevel' => $stockLevel,
            'mightAlsoLike' => $mightAlsoLike,
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|min:3',
        ]);

        $query = $request->input('query');

        // $products = Product::where('name', 'like', "%$query%")
        //                    ->orWhere('details', 'like', "%$query%")
        //                    ->orWhere('description', 'like', "%$query%")
        //                    ->paginate(10);

        $products = Product::search($query)->paginate(10);

        return view('search-results')->with('products', $products);
    }

    public function searchAlgolia(Request $request)
    {
        return view('search-results-algolia');
    }
}
