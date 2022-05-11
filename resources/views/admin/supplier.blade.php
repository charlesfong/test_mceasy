@extends('template_admin.template')

@section('Content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-primary">
        <h4 class="card-title ">List Supplier
          <button type="button" class="btn btn-success" style="float:right" onclick="open_modal_create()">ADD NEW SUPPLIER</button>
        </h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="list_datas" class="table table-striped table-no-bordered table-hover dataTable dtr-inline" cellspacing="0" width="100%" style="width: 100%;" role="grid" aria-describedby="datatables_info">
            <thead class=" text-primary">
              {{-- <th class='text-left'>
                No
              </th> --}}
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
              {{-- <th class='text-center'>
                Details
              </th> --}}
              <th class='text-center'>
                &nbsp;
              </th>
            </thead>
            <tbody id='main_table'>
              @foreach ($suppliers as $key => $value)
                <tr>
                    {{-- <td>{{$key+1}}</td> --}}
                    <td>{{$value->name}}</td>
                    <td>{{$value->address}}</td>
                    <td>{{$value->phone1}}</td>
                    <td>{{$value->email}}</td>
                    {{-- <td class='text-center'>
                        <button class="btn btn-primary btn-fab btn-fab-mini btn-round" onclick="open_detail('{{$value->id}}')">
                            <i class="material-icons">view_list</i>
                        </button>
                    </td> --}}
                    <td class='text-center'>
                      <button class="btn btn-danger btn-fab btn-fab-mini btn-round" onclick="open_delete('{{$value->id}}','{{$value->name}}')">
                          <i class="material-icons">delete</i>
                      </button>
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

<div class="modal fade" tabindex="-1" role="" id='modal_create'>
    <div class="modal-dialog modal-login" role="document">
        <div class="modal-content">
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title ">Add New Supplier</h4>
                </div>
                <div class="modal-body">
                    <form class="form" method="post" id="form_modal_create">
                        <div class="card-body">

                            <div class="form-group bmd-form-group">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">business</i></div>
                                  </div>
                                  <input type="text" name='name' id='cust-name' class="form-control input_form" placeholder="Name...">
                                  <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                </div>
                            </div>

                            <div class="form-group bmd-form-group">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">home</i></div>
                                  </div>
                                  <input type="text" name='address' id='cust-address' class="form-control input_form" placeholder="Address...">
                                </div>
                            </div>
                          
                            <div class="form-group bmd-form-group">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">contact_phone</i></div>
                                  </div>
                                  <input type="text" name='phone1' id='cust-phone1' class="form-control input_form" placeholder="Phone 1...">
                                </div>
                            </div>

                            <div class="form-group bmd-form-group">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">contact_phone</i></div>
                                  </div>
                                  <input type="text" name='phone2' id='cust-phone2' class="form-control input_form" placeholder="Phone 2...">
                                </div>
                            </div>

                            <div class="form-group bmd-form-group">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <div class="input-group-text"><i class="material-icons">contact_phone</i></div>
                                </div>
                                <input type="text" name='phone3' id='cust-phone3' class="form-control input_form" placeholder="Phone 3...">
                              </div>
                            </div>

                            <div class="form-group bmd-form-group">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <div class="input-group-text"><i class="material-icons">email</i></div>
                                </div>
                                <input type="text" name='email' id='cust-email' class="form-control input_form" placeholder="Email...">
                              </div>
                            </div>
                        </div>
                    </form> 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="save_data()">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script src="{{ asset('/template_admin/js/core/jquery.min.js') }}"></script>
<script>

    $(document).ready( function () {
        $("#list_datas").DataTable({
            // searching: false,bLengthChange: false,rowReorder: true,ordering: false
            searching: true,bLengthChange: false,rowReorder: true
        });
        $('#cust-phone1').mask('000-000-000-000');
        $('#cust-phone2').mask('000-000-000-000');
        $('#cust-phone3').mask('000-000-000-000');
    });

    // function open_detail(id){
    //     $("#modal_details").modal('show');
    // }
    
    function save_reset_field(){
        $('.input_form').each(function(){
            this.value = null;
        });
    }

    function save_data() {
        var form = $("#form_modal_create");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: "{{route('save_supplier')}}",
            data: form.serialize(),
            success: function (data) {
                if (data.success){
                  $("#list_datas").dataTable().fnDestroy()
                  $("#main_table").html('');
                  var string_html = '';
                  for (var x = 0; x < data.data.length; x++) {
                    data.data[x]['address'] = data.data[x]['address']==null?'-':data.data[x]['address'];
                    data.data[x]['phone1'] = data.data[x]['phone1']==null?'-':data.data[x]['phone1'];
                    data.data[x]['email'] = data.data[x]['email']==null?'-':data.data[x]['email'];
                    var btn = "<td class='text-center'><button class='btn btn-primary btn-fab btn-fab-mini btn-round' onclick=open_detail('"+data.data[x]['id']+"')><i class='material-icons'>view_list</i></button></td>";
                    var btn_del = "<td class='text-center'><button class='btn btn-danger btn-fab btn-fab-mini btn-round' onclick=open_delete('"+data.data[x]['id']+"','"+data.data[x]['name']+"')><i class='material-icons'>delete</i></button></td>";
                    string_html += "<tr><td>"+data.data[x]['name']+"</td><td>"+data.data[x]['address']+"</td><td>"+data.data[x]['phone1']+"</td><td>"+data.data[x]['email']+"</td>"+btn_del+"</tr>";
                  }
                  $("#main_table").html(string_html);
                  $("#modal_create").modal('hide');
                  
                  $("#list_datas").DataTable({
                      searching: false,bLengthChange: false,rowReorder: false,ordering: false
                  });
                  save_reset_field();
                  $("#modal_success").modal('show');
                }
            },
        });
    }

    function open_delete(id,name) {
      $("#id_delete").val(id);
      $("#modal_delete_info").html("("+name+")");
      $("#modal_delete").modal('show');
    }

    function go_delete() {
      var id = $("#id_delete").val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: "{{route('delete_supplier')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                'id': id
            },
            success: function (data) {
                if (data.success){
                  $("#list_datas").dataTable().fnDestroy()
                  $("#main_table").html('');
                  var string_html = '';
                  for (var x = 0; x < data.data.length; x++) {
                    data.data[x]['address'] = data.data[x]['address']==null?'-':data.data[x]['address'];
                    data.data[x]['phone1'] = data.data[x]['phone1']==null?'-':data.data[x]['phone1'];
                    data.data[x]['email'] = data.data[x]['email']==null?'-':data.data[x]['email'];
                    var btn = "<td class='text-center'><button class='btn btn-primary btn-fab btn-fab-mini btn-round' onclick=open_detail('"+data.data[x]['id']+"')><i class='material-icons'>view_list</i></button></td>";
                    var btn_del = "<td class='text-center'><button class='btn btn-danger btn-fab btn-fab-mini btn-round' onclick=open_delete('"+data.data[x]['id']+"','"+data.data[x]['name']+"')><i class='material-icons'>delete</i></button></td>";
                    string_html += "<tr><td>"+data.data[x]['name']+"</td><td>"+data.data[x]['address']+"</td><td>"+data.data[x]['phone1']+"</td><td>"+data.data[x]['email']+"</td>"+btn_del+"</tr>";
                  }
                  $("#main_table").html(string_html);
                  $("#modal_delete").modal('hide');
                  
                  $("#list_datas").DataTable({
                      searching: false,bLengthChange: false,rowReorder: true,ordering: false
                  });
                }
            },
        });
    }
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

    function open_modal_create() {
      $("#modal_create").modal('show');
    }
</script>