@extends('layouts.master')

@section('title', $title)

@push('vueapp')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
    type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<section class="section">
    <div class="row" id="basic-table">
        <div class="col-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{$title}}</h4>
                </div>
                <div class="card-content">
                  <div class="card-body">
                    <div class="table-responsive">
                        <table id="myTable" class="table table-lg">
                            <thead>
                                <tr>
                                    <th>No Review</th>
                                    <th>Tanggal</th>
                                    <th>Score</th>
                                    <th>Keterangan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    function getPredicate($score) {
                                        if ($score == 5) return 'Sangat Baik';
                                        if ($score < 5 && $score >= 4) return 'Baik';
                                        if ($score < 4 && $score >= 3) return 'Cukup';
                                        if ($score < 3 && $score >= 2) return 'Buruk';
                                        if ($score < 2) return 'Sangat Buruk';
                                        return 'Error, Nilai Diluar Jangkauan';
                                    }
                                @endphp
                                @foreach ($data as $item )
                                <tr>
                                        <td>{{$item->No_Review}}</td>
                                        <td>{{date('j F Y', strtotime($item->Date))}}</td>
                                        <td>{{ number_format($item->score, 2). '/'.getPredicate($item->score) }}</td>
                                        <td>{{$item->Keterangan}}</td>
                                        <td>
                                            <a class="btn btn-info btn-sm" href="{{ route('cross.feedback.Detail', [str_replace('/', '@', $item->No_Review)]) }}">Details</a>

                                        </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>

                  </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Edit -->


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

    //
</script>

@endsection

@push('scripts')

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script>


    $(document).ready(function() {
        $('#myTable').DataTable({
            pageLength: 5, // default tampil 5 row
            lengthMenu: [5, 10, 25, 50, 100]
        });
    });
</script>

@endpush
