<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;
use Session;
class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mightAlsoLike = Product::mightAlsoLike()->get();
        return view('cart')->with([
            'mightAlsoLike' => $mightAlsoLike,
            'discount' => getNumbers()->get('discount'),
            'newSubtotal' => getNumbers()->get('newSubtotal'),
            'newTax' => getNumbers()->get('newTax'),
            'newTotal' => getNumbers()->get('newTotal'),
        ]);
    }

    public function cartstep(Product $product,Request $request)
    {
        if ($request->sendbtn=='op1') {
            return view('upload')->with(['productID' =>$request->prdtID,'productCODE' =>$request->prdtcode,'productDESCRIPTION' =>$request->prddesc,'productRUNSIZE' =>$request->prdRunsize,'productSIDE' =>$request->prdside,'productTATIME' =>$request->prdTurnAroundTime,'productPRICE' =>$request->prdtprice,'produto'=>$product,'optionuuid' =>$request->option_uuid,'colorspecuuid' =>$request->colorspec_uuid,'runsizeuuid' =>$request->runsize_uuid]); 
        }
        if ($request->sendbtn=='op3') {
            return view('we-designed')->with(['productID' =>$request->prdtID,'productCODE' =>$request->prdtcode,'productDESCRIPTION' =>$request->prddesc,'productRUNSIZE' =>$request->prdRunsize,'productSIDE' =>$request->prdside,'productTATIME' =>$request->prdTurnAroundTime,'produto'=>$product,'optionuuid' =>$request->option_uuid,'colorspecuuid' =>$request->colorspec_uuid,'runsizeuuid' =>$request->runsize_uuid]);
        }

       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function store(Product $product,Request $request)
    {
        $Authid = Auth::id();
        // $duplicates = Cart::search(function ($cartItem, $rowId) use ($product) {
        //     return $cartItem->id === $product->id;
        // });
        // if ($duplicates->isNotEmpty()) {
        //     return redirect()->route('cart.index')->with('success_message', 'Item is already in your cart!');
        // }
        $produtprice = get4overprices($request->prdtID,$request->prdRunsize,$request->prdside,$request->prdTurnAroundTime);
        if ($request->fileM[0]==null) {
        $filename="clt".$Authid."-".time()."-".date('Ymd');
        $imagenA=$request->archivo;
        $nombre_archivoF =$request->archivo->getClientOriginalExtension();
        $nombre_archivoF=$filename.'F.'.$nombre_archivoF;
        $imagenA->move('storage/Userfiles/',$nombre_archivoF);
        Cart::add($product->id, $product->name, 1, printingPrice($produtprice),['shiping' =>  0,'shipingTp' => 'N/A','decription' =>  $request->prddesc,'imgF' =>$nombre_archivoF,'imgB' =>'N/A','side' =>$request->prdside,'quantity' =>$request->prdRunsize,'tat' =>$request->prdTurnAroundTime,'produtcode' =>$request->prdtcode,'produtid' =>$request->prdtID,'optionuuid' =>$request->option_uuid,'colorspecuuid' =>$request->colorspec_uuid,'runsizeuuid' =>$request->runsize_uuid]);
        return redirect()->route('cart.index')->with('success_message', 'Item was added to your cart!');
        }else{
            $filename="clt".$Authid."-".time()."-".date('Ymd');
            $imagenF=$request->fileM[0];
            $imagenB=$request->fileM[1];
            $nombre_archivoF =$request->fileM[0]->getClientOriginalExtension();
            $nombre_archivoB =$request->fileM[0]->getClientOriginalExtension();
            $nombre_archivoF=$filename.'F.'.$nombre_archivoF;
            $nombre_archivoB=$filename.'B.'.$nombre_archivoB;
            $imagenF->move('storage/Userfiles/',$nombre_archivoF);
            $imagenB->move('storage/Userfiles/',$nombre_archivoB);
            Cart::add($product->id, $product->name, 1, printingPrice($produtprice),['shiping' => 0,'shipingTp' => 'N/A','decription' =>  $request->prddesc,'imgF' =>$nombre_archivoF,'imgB' =>$nombre_archivoB,'side' =>$request->prdside,'quantity' =>$request->prdRunsize,'tat' =>$request->prdTurnAroundTime,'produtcode' =>$request->prdtcode,'produtid' =>$request->prdtID,'optionuuid' =>$request->option_uuid,'colorspecuuid' =>$request->colorspec_uuid,'runsizeuuid' =>$request->runsize_uuid]);
        return redirect()->route('cart.index')->with('success_message', 'Item was added to your cart!');
        }
        
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|between:1,5'
        ]);

        if ($validator->fails()) {
            session()->flash('errors', collect(['Quantity must be between 1 and 5.']));
            return response()->json(['success' => false], 400);
        }

        if ($request->quantity > $request->productQuantity) {
            session()->flash('errors', collect(['We currently do not have enough items in stock.']));
            return response()->json(['success' => false], 400);
        }

        Cart::update($id, $request->quantity);
        session()->flash('success_message', 'Quantity was updated successfully!');
        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Cart::remove($id);

        return back()->with('success_message', 'Item has been removed!');
    }

    /**
     * Switch item for shopping cart to Save for Later.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function switchToSaveForLater($id)
    {
        $item = Cart::get($id);

        Cart::remove($id);

        $duplicates = Cart::instance('saveForLater')->search(function ($cartItem, $rowId) use ($id) {
            return $rowId === $id;
        });

        if ($duplicates->isNotEmpty()) {
            return redirect()->route('cart.index')->with('success_message', 'Item is already Saved For Later!');
        }
        Cart::instance('saveForLater')->add($item->id, $item->name, 1, $item->price)
            ->associate('App\Product');

        return redirect()->route('cart.index')->with('success_message', 'Item has been Saved For Later!');
    }
}
