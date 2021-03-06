@extends('layout')

@section('title', 'Checkout')

@section('extra-css')
    <style>
        .mt-32 {
            margin-top: 32px;
        }
    </style>
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
    <script src="https://js.stripe.com/v3/"></script>

@endsection

@section('content')

    <div class="container">

        @if (session()->has('success_message'))
            <div class="spacer"></div>
            <div class="alert alert-success">
                {{ session()->get('success_message') }}
            </div>
        @endif

        @if(count($errors) > 0)
            <div class="spacer"></div>
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="checkout-heading stylish-heading">Checkout</h1>
        <div class="checkout-section" ng-controller="checkoutcontroller">
            <script>
             
            

            </script>
            <div>
                <form action="{{ route('checkout.Authorize') }}" method="POST" id="payment-form">
                    {{ csrf_field() }}
                
                    <div ng-show="PaymentDetails">
                    <h2>Billing Details</h2>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        @if (auth()->user())
                            <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->email }}" readonly>
                        @else
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        @if (auth()->user())
                        <input type="text" class="form-control" id="name" name="name" value="{{ auth()->user()->name }}" required>
                        @else
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        @if (auth()->user())
                        <input type="text" ng-init="address = '{{ auth()->user()->Address }}'" class="form-control" id="address" name="address" ng-model="address" required>
                        @else
                        <input type="text" ng-init="address = '{{ old('address') }}'" class="form-control" id="address" name="address" value="{{ old('address') }}" ng-model="address" required>
                        @endif
                    </div>
                    <div class="half-form"> 
                        <div class="form-group">
                            <label for="city">City</label>
                            @if (auth()->user())
                            <input type="text"  ng-init="city = '{{ auth()->user()->City }}'"  class="form-control" id="city" name="city" ng-model="city"  required>
                            @else
                            <input type="text"  ng-init="city = '{{ old('city') }}'" class="form-control" id="city" name="city" value="{{ old('city') }}" ng-model="city"  required>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="province">Province</label>
                            @if (auth()->user())
                            <input type="text"  ng-init="province = '{{ auth()->user()->Province }}'" class="form-control" id="province" name="province"  ng-model="province"  required>
                            @else
                            <input type="text"  ng-init="province = '{{ old('province') }}'" class="form-control" id="province" name="province" value="{{ old('province') }}" ng-model="province"  required>
                            @endif
                        </div>
                    </div> <!-- end half-form -->
                    <div class="half-form">
                        <div class="form-group">
                            <label for="postalcode">Postal Code</label>
                            @if (auth()->user())
                            <input type="text"  ng-init="postalcode = '{{ auth()->user()->PostalCode }}'" class="form-control" id="postalcode" name="postalcode" ng-model="postalcode" ng-change="computeshipping()" required>
                            @else
                            <input type="text"  ng-init="postalcode = '{{ old('postalcode') }}'" class="form-control" id="postalcode" name="postalcode" value="{{ old('postalcode') }}" ng-model="postalcode" ng-change="computeshipping()" required>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            @if (auth()->user())
                            <input type="text"  ng-init="phone = '{{ auth()->user()->Phone }}'" class="form-control" id="phone" name="phone"  ng-model="phone" required>
                            @else
                            <input type="text"  ng-init="phone = '{{ old('phone') }}'" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" ng-model="phone" required>
                            @endif
                        </div>
                        <input hidden type="text" class="form-control" id="ShippingMethod" name="Shippingmethod" value="@{{ShippingMethod}}" required>
                     </div> <!-- end half-form -->
                     <div class="spacer"></div>
                     @if (Cart::count() > 1)
                      <h2>Shipping Method for all items</h2>
                      @else
                      <h2>Shipping Method  </h2>
                      @endif
                      <div id="preloader" ng-hide="preloader">
                   <div class="flexloader">
                       <div class="loader">
                           <i class="fa fa-cog fa-4x yellow">
                           </i>
                           <i class="fa fa-cog fa-4x black">
                           </i>
                        </div>
                    </div>
                    <p class="Calculating "> Calculating Shipping...
This may take a few moments.</p>
                </div>
                <div class="alert alert-warning" role="alert" ng-hide="dangermesagge">
  @{{danger}}
</div>               
            <div class="shippingResult" id="div_shipping" ng-repeat="ship in shipping_options | unique: 'service_name'">
                <label class="col-md-12 label-result">
                    <div class="shippingServices clearfix">
                        <input id="ship_FEDEX_GROUND" type="radio" class="shipRadio" style="outline: none;" name="shipping_selected" value=" @{{(ship.service_price)}}" ng-click="shipingupdate(ship.service_price,ship.service_name)">&nbsp;
                        <!--checking of local pickup options-->
                         <label for="ship_FEDEX_GROUND" class="shipping-cost shipping-rate-wrap" style="float:right">
                              @{{(ship.service_price)}}
                            </label>
                          <label for="ship_FEDEX_GROUND" class="shipping-detail">@{{ship.service_name}}&nbsp;</label>
                     </div>
                </label>
            </div>
                    <div class="spacer"></div>
                   
                    </div>
<div class="PaymentDetails" ng-hide="PaymentDetails">
<h2>Payment Details</h2>
                <div class="form-group">
                    <label for="cnumber">Card Number</label>
                    <input type="text" class="form-control" id="cnumber" name="cnumber" placeholder="Enter Card Number">
                </div>
                <div class="form-group">
                  <label for="card-expiry-month">Expiration Month</label>
                  {{ Form::selectMonth(null, null, ['name' => 'card_expiry_month', 'class' => 'form-control', 'required']) }}
                </div>
                <div class="form-group">
                  <label for="card-expiry-year">Expiration Year</label>
                  {{ Form::selectYear(null, date('Y'), date('Y') + 10, null, ['name' => 'card_expiry_year', 'class' => 'form-control', 'required']) }}
                </div>
                <div class="form-group">
                    <label for="ccode">Card Code</label>
                    <input type="text" class="form-control" id="ccode" name="ccode" placeholder="Enter Card Code">
                </div>
                    <div class="spacer"></div>
                    <button type="submit" id="complete-order" class="button-primary full-width">Complete Order</button>

</div>
              

                    </form>
                    <button  id="Continue-order" class="button-primary full-width" ng-click="Continueorder()">Continue</button>
<!-- Modal -->
<div class="modal fade" id="candidates" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Shipping Address Validation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      Sorry! We were not able to verify your shipping address. Please 
      please select one of our suggestions or modify it and try again
      <div class="suggestions" ng-repeat="adr in addresscandidates " ng-click="setaddres(adr.address,adr.city,adr.state,adr.zipcode)"> @{{adr.address}} @{{adr.city}},  @{{adr.state}} @{{adr.zipcode}}</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit Address</button>
      </div>
    </div>
  </div>
</div>

<div class="flex-center position-ref full-height">
<div class="content">

</div>
    </div>




                         
                @if ($paypalToken)
                    <div class="mt-32" ng-hide="PaymentDetails">or</div>
                    <div class="mt-32" ng-hide="PaymentDetails">
                        <h2>Pay with PayPal</h2>
                        <form method="post" id="paypal-payment-form" action="{{ route('checkout.paypal') }}">
                            @csrf
                            <section>
                                <div class="bt-drop-in-wrapper">
                                    <div id="bt-dropin"></div>
                                </div>
                            </section>
                            <input hidden type="text" class="form-control" id="ShippingMethod" name="Shippingmethod" value="@{{ShippingMethod}}" required>
                            <input id="nonce" name="payment_method_nonce" type="hidden" />
                            <button class="button-primary" type="submit"><span>Pay with PayPal</span></button>
                        </form>
                    </div>
                @endif
            </div>
            <div class="checkout-table-container">
                <h2>Your Order</h2>
                <div class="checkout-table">
                    @foreach (Cart::content() as $item)
                    <div class="checkout-table-row">
                        <div class="checkout-table-row-left">
                            <div class="checkout-item-details">
                                <div class="checkout-table-item">{{ $item->name }}</div>
                                <div class="checkout-table-description">{{ $item->options->decription }}</div>
                                
                            </div>
                        </div> <!-- end checkout-table -->

                        <div class="checkout-table-row-right">
                            <div class="checkout-table-quantity">{{ $item->options->quantity }}</div>
                        </div>
                    </div> <!-- end checkout-table-row -->
                    @endforeach

                </div> <!-- end checkout-table -->

                <div class="checkout-totals">
                    <div class="checkout-totals-left">
                        Subtotal <br>
                        @if (session()->has('coupon'))
                            Discount ({{ session()->get('coupon')['name'] }}) :
                            <br>
                            <hr>
                            New Subtotal <br>
                        @endif
                        Tax ({{config('cart.tax')}}%)<br>
                        Shipping <br>
                        <span class="checkout-totals-total">Total</span>

                    </div>

                    <div class="checkout-totals-right">
                        {{ presentPrice(Cart::subtotal()) }} <br>
                        @if (session()->has('coupon'))
                            -{{ presentPrice($discount) }} <br>
                            <hr>
                            {{ presentPrice($newSubtotal) }} <br>
                        @endif
                        {{ presentPrice($newTax) }} <br>
                        {{ presentPrice(getshipingNumbers()) }} <br>
                        <span class="checkout-totals-total">{{ presentPrice($newTotal) }}</span>

                    </div>
                </div> <!-- end checkout-totals -->
            </div>

        </div> <!-- end checkout-section -->
    </div>
@endsection
@section('extra-js')
    <script src="https://js.braintreegateway.com/web/dropin/1.13.0/js/dropin.min.js"></script>
    <script src="{{ asset('js/checkout.js') }}"></script>
    <script>

        (function(){
            
            // PayPal Stuff
            var form = document.querySelector('#paypal-payment-form');
            var client_token = "{{ $paypalToken }}";

            braintree.dropin.create({
              authorization: client_token,
              selector: '#bt-dropin',
              paypal: {
                flow: 'vault'
              }
            }, function (createErr, instance) {
              if (createErr) {
                console.log('Create Error', createErr);
                return;
              }

              // remove credit card option
              var elem = document.querySelector('.braintree-option__card');
              elem.parentNode.removeChild(elem);

              form.addEventListener('submit', function (event) {
                event.preventDefault();

                instance.requestPaymentMethod(function (err, payload) {
                  if (err) {
                    console.log('Request Payment Method Error', err);
                    return;
                  }

                  // Add the nonce to the form and submit
                  document.querySelector('#nonce').value = payload.nonce;
                  form.submit();
                });
              });
            });

        })();
    </script>
@endsection
