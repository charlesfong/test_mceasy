@extends('template_admin.template')

@section('Content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-primary">
        <h4 class="card-title ">List Customers</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="list_customers" class="table table-striped table-no-bordered table-hover dataTable dtr-inline" cellspacing="0" width="100%" style="width: 100%;" role="grid" aria-describedby="datatables_info">
            <thead class=" text-primary">
              <th class='text-left'>
                No
              </th>
              <th class='text-left'>
                Name
              </th>
              <th class='text-left'>
                Address
              </th>
              <th class='text-left'>
                Phone
              </th>
              <th class='text-left'>
                Email
              </th>
              <th class='text-center'>
                Details
              </th>
            </thead>
            <tbody>
              {{-- @foreach ($customers as $key => $value)
                <tr>
                    <td>{{$key+1}}</td>
                    <td>{{$value->name}}</td>
                    <td>{{$value->address}}</td>
                    <td>{{$value->phone1}}</td>
                    <td>{{$value->email}}</td>
                    <td class='text-center'>
                        <button class="btn btn-primary btn-fab btn-fab-mini btn-round" onclick="open_detail('{{$value->id}}')">
                            <i class="material-icons">view_list</i>
                        </button>
                    </td>
                </tr>
              @endforeach --}}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id='modal_details'>
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Contact Person</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
              <tr>
                  <th class="text-left">Name</th>
                  <th class="text-left">Phone 1</th>
                  <th class="text-left">Phone 2</th>
                  <th class="text-left">Email</th>
              </tr>
          </thead>
          <tbody id='modal_details_body'>
              {{-- <tr>
                  <td class="text-left">Amin</td>
                  <td class="text-left">085236057632</td>
                  <td class="text-left"></td>
                  <td class="td-actions text-right">
                      <button type="button" rel="tooltip" class="btn btn-fab btn-info">
                          <i class="material-icons">person</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-fab btn-success">
                          <i class="material-icons">edit</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-fab btn-danger">
                          <i class="material-icons">close</i>
                      </button>
                  </td>
              </tr> --}}
          </tbody>
        </table>
      </div>
      
    </div>
  </div>
</div>
@endsection

<script src="{{ asset('/template_admin/js/core/jquery.min.js') }}"></script>
<script>
    $(document).ready( function () {
        $("#list_customers").DataTable({
            searching: false,bLengthChange: false,rowReorder: true
        });
    });
    // function open_detail(id){
    //     $("#modal_details").modal('show');
    // }
    function open_detail(id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: "{{route('show_contact_person')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                'id': id
            },
            success: function (data) {
                var success = data['result'];
                var data    = data['data'];
                var html_   = "";
                for (var x = 0; x < data.length; x++) {
                    var tooltips = "<td class='td-actions text-right'>"+
                      "<button type='button' rel='tooltip' class='btn btn-fab btn-info'>"+
                          "<i class='material-icons'>person</i>"+
                      "</button>"+
                      "<button type='button' rel='tooltip' class='btn btn-fab btn-success'>"+
                          "<i class='material-icons'>edit</i>"+
                      "</button>"+
                      "<button type='button' rel='tooltip' class='btn btn-fab btn-danger'>"+
                          "<i class='material-icons'>close</i>"+
                      "</button>"+
                    "</td>";
                    html_ += "<tr><td class='text-left'>"+data[x]['name']+"</td><td class='text-left'>"+data[x]['phone_1']+"</td><td class='text-left'>"+data[x]['phone_2']+"</td><td class='text-left'>"+data[x]['email_']+"</td>"+tooltips+"</tr>";
                    console.log(data[x]);
                }
                $("#modal_details_body").html(html_);
                // var emailPembeli = data['email'];
                // var telpPembeli = data['phone'];
                // var namaPembeli = data['name'];
                // $("#edit-id").val(id);
                // $("#edit-name").val(namaPembeli);
                // $("#edit-address").val(tujuanPengiriman);

                $("#modal_details").modal('show');
            },
        });
    }
</script>