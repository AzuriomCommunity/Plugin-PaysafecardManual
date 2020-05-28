@extends('layouts.app')

@section('title', 'PaysafeCard Manual')

@section('content')
    <div class="container content">
        @error('code_1','code_2','code_3','code_4')
        <span class="invalid-feedback" role="alert"><strong>4 characters in each section</strong></span>
        @enderror
        <div class="card shadow mb-4">
            <div class="card-body center">
                <form action="{{ route('paysafecardmanual.pay') }}" method="post">
                    @csrf
                    <div class="form-group row" style="justify-content: center;">
                        <input pattern=".{4}" required class="form-control col-2" type="text" name="code_1"> -
                        <input pattern=".{4}" required class="form-control col-2" type="text" name="code_2"> -
                        <input pattern=".{4}" required class="form-control col-2" type="text" name="code_3"> -
                        <input pattern=".{4}" required class="form-control col-2" type="text" name="code_4">
                    </div>
                    
                    <input type="hidden" name="payment_id" value="{{ $payment_id }}">
                    <button type="submit">Payer</button>
                </form>
            </div>
        </div>
   
    </div>
@endsection
