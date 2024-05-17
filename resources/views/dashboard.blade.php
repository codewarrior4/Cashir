@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="pc-container">
    <div class="pc-content">
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard/index.html">Home</a></li>
                
            </div>
            <div class="col-md-12">
              <div class="page-header-title">
                <h2 class="mb-0">Finance</h2>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->
      <!-- [ Main Content ] start -->
      <div class="row">
        <div class="card">
          <div class="card-header">
            <h5>Toggle Payment Gateways</h5>
          </div>
          <div class="card-body">
            <form class="row row-cols-md-auto g-3 align-items-center" method="POST" action="{{ route('update-gateways') }}">
              <div class="col-12">
                @csrf
                <div class="form-check">
                  <input name="paystack" value="1" class="form-check-input" @if ($gateways->paystack == 1)
                  checked
                @endif type="checkbox" id="inlineFormCheck">
                  <label class="form-check-label" for="inlineFormCheck" > Paystack </label>
                </div>
                <div class="form-check">
                  <input name="monnify" value="1" class="form-check-input" @if ($gateways->monnify == 1)
                  checked
                @endif type="checkbox" id="inlineFormCheck">
                  <label class="form-check-label"  for="inlineFormCheck"> Monnify </label>
                </div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">Update</button>
              </div>
            </form>
          </div>
          <i class="text-info">* Turn Payment gateways off to test other payment methods</i> <br>
        </div>
        <div class="col-sm-12">
          
          <!-- HTML Input Types -->
          <div class="card">
            <div class="card-header">
              <h5>HTML Input Types</h5>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('pay') }}" accept-charset="UTF-8" class="form-horizontal" role="form">
                <div class="mb-3">
                  @csrf
                  <label for="demo-text-input" class="form-label">Title</label>
                  <input class="form-control" type="text" max="30" value="John Doe" placeholder="demo-text-input">
                </div>
                  <div class="mb-3">
                    <label for="demo-number-input" n class="form-label">Amount</label>
                    <input class="form-control" name="amount" type="text" value="120000" id="demo-number-input">
                  </div>
                  <div class="mb-3">
                    <label for="demo-datetime-local" class="form-label">Set Date</label>
                    <input class="form-control" type="datetime-local" value="2021-12-31T04:03:20" id="demo-datetime-local">
                  </div>
                  <input type="hidden" name="email" value="{{ Auth::user()->email }}"> {{-- required --}}
                
                  <input type="hidden" name="currency" value="NGN">
                <input type="hidden" name="reference" value="{{ Paystack::genTranxRef() }}"> {{-- required --}}
                <input type="hidden" name="metadata" value="{{ json_encode($array = ['key_name' => 'value']) }}"> {{-- optional --}}
                <input type="hidden" name="key" value="{{ config('paystack.secretKey') }}"> {{-- required --}}
                </div>
                <div class="card-footer pt-3">
                  <button class="btn btn-primary me-2">Proceed To Payment</button>
                  <button type="reset" class="btn btn-secondary">Cancel</button>
                </div>
              </form>
          </div>
        </div>
        <div class="col-md-7 col-xxl-8">
          <div class="row">
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                      <h6 class="mb-0">Transactions</h6>
                      <p class="mb-0 text-muted">Entire Transaction</p>
                    </div>
                    <div class="dropdown">
                      <a class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none" href="#"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-v f-18"></i>
                      </a>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#" data-period="today">Today</a>
                        <a class="dropdown-item" href="#" data-period="weekly">Weekly</a>
                        <a class="dropdown-item" href="#" data-period="monthly">Monthly</a>
                        <a class="dropdown-item" href="#" data-period="yearly">Year</a>
                    </div>
                    </div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                    <h4 class="mb-0">&#8358; {{ number_format($transactions->sum('amount'),2) }}</h4>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header">
              <h5>Transactions</h5>
             
            </div>
            <div class="card-body">
              <div class="dt-responsive">
                <table id="dom-jqry" class="table table-striped table-responsive table-bordered nowrap">
                  <thead>
                    <tr>
                      <td>SN</td>
                      <th>Description</th>
                      <th>Payment Gateway</th>
                      <th>Status</th>
                      <th>Amount</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                      @foreach ($transactions as $key => $transaction)
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $transaction->title }}</td>
                        <td>{{ $transaction->payment_method }}</td>
                        <td>
                            @if ($transaction->status == 'Completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif ($transaction->status == 'Failed')
                                <span class="badge bg-danger">Failed</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                        <td>&#8358; {{ number_format($transaction->amount,2) }}</td>
                        <td>{{ $transaction->created_at->format('d M, Y h:i ') }}</td>
                      </tr>
                        
                      @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <td>SN</td>

                      <th>Description</th>
                      <th>Payment Gateway</th>
                      <th>Status</th>
                      <th>Amount</th>
                      <th>Date</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>

  @endsection