@extends('template_admin.template')

@section('Content')
<style>
  td{
    cursor: pointer;
  }
</style>
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-primary">
        <h4 class="card-title ">List Karyawan
          <button type="button" class="btn btn-success" style="float:right" onclick="open_modal_create()">Tambah Karyawan</button>
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
                Nomor Induk
              </th>
              <th class='text-left'>
                Nama
              </th>
              <th class='text-left'>
                Alamat
              </th>
              <th class='text-left'>
                Tanggal Lahir
              </th>
              <th class='text-left'>
                Tanggal Bergabung
              </th>
              <th class='text-left'>
                Sisa Cuti
              </th>
              <th class='text-center'>
                {{-- Details --}}
              </th>
            </thead>
            <tbody id='main_table'>
              @foreach ($karyawan as $key => $value)
                <tr>
                    {{-- <td>{{$key+1}}</td> --}}
                    <td class="td_nomor_induk"   id="td_nomor_induk{{$value->nomor_induk}}">{{$value->nomor_induk}}</td>
                    <td class="td_name"   id="td_name_{{$value->nomor_induk}}">{{$value->nama}}</td>
                    <td class="td_addr"   id="td_addr_{{$value->nomor_induk}}">{{$value->alamat}}</td>
                    <td class="td_phone1" id="td_phone1_{{$value->nomor_induk}}">{{date('d-M-y',strtotime($value->tanggal_lahir))}}</td>
                    <td class="td_email"  id="td_email_{{$value->nomor_induk}}">{{date('d-M-y',strtotime($value->tanggal_bergabung))}}</td>
                    <td>{{12-App\Models\cuti::where('nomor_induk',$value->nomor_induk)->sum('lama_cuti')}}</td>
                    <td class='text-center' style='width:15%'>
                        <button class="btn btn-warning btn-fab btn-fab-mini btn-round" onclick="open_edit('{{$value->nomor_induk}}')">
                            <i class="material-icons">edit</i>
                        </button>
                        {{-- <button class="btn btn-primary btn-fab btn-fab-mini btn-round" onclick="open_detail('{{$value->nomor_induk}}')">
                          <i class="material-icons">view_list</i>
                        </button> --}}
                        <button class="btn btn-danger btn-fab btn-fab-mini btn-round" onclick="open_delete('{{$value->nomor_induk}}','{{$value->nama}}')">
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
      <div class="modal-header row">
        <h5 class="modal-title col-md-3">
          Company
        </h5>
        <span class="col-md-8">
            <button type="button" class="btn btn-success btn-sm" style="float:right" onclick="open_modal_create_address()">ADD NEW COMPANY</button>
        </span>
        <button type="button" class="close col-md-1" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-hover">
          <thead>
              <tr>
                  <th class="text-left">No</th>
                  <th class="text-left">Name</th>
                  <th class="text-left">Address</th>
                  <input type="hidden" id="id_customer_">
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
                    <h4 class="card-title ">Tambah Karyawan</h4>
                </div>
                <div class="modal-body">
                    <form class="form" method="post" id="form_modal_create">
                        <div class="card-body">

                            <div class="form-group bmd-form-group">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">business</i></div>
                                  </div>
                                  <input type="text" name='nama' id='cust-name' class="form-control input_form" placeholder="Nama...">
                                  <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                </div>
                            </div>

                            <div class="form-group bmd-form-group">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">home</i></div>
                                  </div>
                                  <input type="text" name='alamat' id='cust-address' class="form-control input_form" placeholder="Alamat...">
                                </div>
                            </div>
                          
                            <div class="form-group bmd-form-group">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <div class="input-group-text">
                                    <i class="material-icons">person</i>
                                  </div>
                                </div>
                                {{-- <div class='row'> --}}
                                  <div class='col-md-2 col-sm-2 col-lg-2'> 
                                    <select class="form-control" data-style="btn btn-link" id="input-date" name='date_input'>
                                      @php
                                        for ($i=1; $i < 32; $i++) { 
                                          echo("<option value='$i'>$i</option>");
                                        }
                                      @endphp
                                    </select>
                                  </div>
                                  <div class='col-md-4 col-sm-4 col-lg-4'> 
                                    <select class="form-control" data-style="btn btn-link" id="input-month" name='month_input'>
                                      @php
                                        $months=array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
                                        foreach ($months as $key => $value) {
                                          $key++;
                                          echo("<option value='$key'>$value</option>");
                                        }
                                      @endphp
                                    </select>
                                  </div>

                                  <div class='col-md-4 col-sm-4 col-lg-4'> 
                                    <select class="form-control" data-style="btn btn-link" id="input-year" name='year_input'>
                                      @php
                                        $year=date('Y');
                                        for ($i=1900; $i <=$year ; $i++) { 
                                          $selected="";
                                          if ($i==$year) {
                                            $selected="selected";
                                          }
                                          echo("<option value='$i' $selected>$i</option>");
                                        }
                                      @endphp
                                    </select>
                                  </div>
                                
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

<div class="modal fade" tabindex="-1" role="" id='modal_edit'>
  <div class="modal-dialog modal-login" role="document">
      <div class="modal-content">
          <div class="card">
              <div class="card-header card-header-primary">
                  <h4 class="card-title ">Edit Karyawan - <span id='edit-title'></span></h4>
              </div>
              <div class="modal-body">
                  <form class="form" method="post" id="form_modal_edit">
                      <input type="hidden" name='edit_id_karyawan' id='edit-id_karyawan'>
                      <div class="card-body">

                          <div class="form-group bmd-form-group">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <div class="input-group-text"><i class="material-icons">business</i></div>
                                </div>
                                <input type="text" name='edit_name' id='edit-cust-name' class="form-control input_form" placeholder="Name...">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                              </div>
                          </div>

                          <div class="form-group bmd-form-group">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <div class="input-group-text"><i class="material-icons">home</i></div>
                                </div>
                                <input type="text" name='edit_address' id='edit-cust-address' class="form-control input_form" placeholder="Address...">
                              </div>
                          </div>
                        
                          <div class="form-group bmd-form-group">
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <div class="input-group-text">
                                  <i class="material-icons">person</i>
                                </div>
                              </div>
                              {{-- <div class='row'> --}}
                                <div class='col-md-2 col-sm-2 col-lg-2'> 
                                  <select class="form-control" data-style="btn btn-link" id="edit-input-date" name='date_input'>
                                    @php
                                      for ($i=1; $i < 32; $i++) { 
                                        echo("<option value='$i'>$i</option>");
                                      }
                                    @endphp
                                  </select>
                                </div>
                                <div class='col-md-4 col-sm-4 col-lg-4'> 
                                  <select class="form-control" data-style="btn btn-link" id="edit-input-month" name='month_input'>
                                    @php
                                      $months=array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
                                      foreach ($months as $key => $value) {
                                        $key++;
                                        echo("<option value='$key'>$value</option>");
                                      }
                                    @endphp
                                  </select>
                                </div>

                                <div class='col-md-4 col-sm-4 col-lg-4'> 
                                  <select class="form-control" data-style="btn btn-link" id="edit-input-year" name='year_input'>
                                    @php
                                      $year=date('Y');
                                      for ($i=1900; $i <=$year ; $i++) { 
                                        $selected="";
                                        if ($i==$year) {
                                          $selected="selected";
                                        }
                                        echo("<option value='$i' $selected>$i</option>");
                                      }
                                    @endphp
                                  </select>
                                </div>
                              
                            </div>
                          </div>

                      </div>
                  </form> 
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-primary" onclick="save_data('edit-customer')">Save</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="" id='modal_create_address'>
  <div class="modal-dialog modal-login" role="document">
      <div class="modal-content">
          <div class="card">
              <div class="card-header card-header-primary">
                  <h4 class="card-title ">Add New Address</h4>
              </div>
              <div class="modal-body">
                  <form class="form" method="post" id="form_modal_create_address">
                      <input type="hidden" name='id_customer' id='id_customer'>
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
                                <div class="input-group-text"><i class="material-icons">person</i></div>
                              </div>
                              <input type="text" name='cp_name1' id='cust-cpname1' class="form-control input_form" placeholder="Contact Person Name 1...">
                            </div>
                          </div>

                          <div class="form-group bmd-form-group">
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <div class="input-group-text"><i class="material-icons">person</i></div>
                              </div>
                              <input type="text" name='cp_name2' id='cust-cpname2' class="form-control input_form" placeholder="Contact Person Name 2...">
                            </div>
                          </div>

                          <div class="form-group bmd-form-group">
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <div class="input-group-text"><i class="material-icons">person</i></div>
                              </div>
                              <input type="text" name='cp_name3' id='cust-cpname3' class="form-control input_form" placeholder="Contact Person Name 3...">
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

                          {{-- <div class="form-group bmd-form-group">
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <div class="input-group-text"><i class="material-icons">email</i></div>
                              </div>
                              <input type="text" name='email' id='cust-email' class="form-control input_form" placeholder="Email...">
                            </div>
                          </div> --}}
                      </div>
                  </form> 
              </div>
              <div class="modal-footer">
                  <button type="button" id='modal_save_button' class="btn btn-primary" onclick="save_data('Address')">Save</button>
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
        $('#cust-phone1').mask('000-000-000-000-000');
        $('#cust-phone2').mask('000-000-000-000-000');
        $('#cust-phone3').mask('000-000-000-000-000');

        $('#edit-cust-phone1').mask('000-000-000-000-000');
        $('#edit-cust-phone2').mask('000-000-000-000-000');
        $('#edit-cust-phone3').mask('000-000-000-000-000');
    });
    // function open_detail(id){
    //     $("#modal_details").modal('show');
    // }
    
    $(".td_name").dblclick(function(){
      alert("The paragraph was double-clicked");
    });

    function save_reset_field(){
        $('.input_form').each(function(){
            this.value = null;
        });
    }

    function save_data(mode=null) {
      console.log(mode);
        $("#modal_save_button").prop('disabled', true);
        var form = $("#form_modal_create");
        var url  = "{{route('save_karyawan')}}";
        if (mode=='edit-customer'){
            url = "{{route('update_karyawan')}}";
            form= $("#form_modal_edit");
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url:  url,
            data: form.serialize(),
            success: function (data) {
                if (data.success){
                  $("#list_datas").dataTable().fnDestroy()
                  $("#main_table").html('');
                  var string_html = '';
                  for (var x = 0; x < data.data.length; x++) {
                    var btn_del = "<button class='btn btn-danger btn-fab btn-fab-mini btn-round' onclick=open_delete('"+data.data[x]['nomor_induk']+"','"+data.data[x]['nama']+"')><i class='material-icons'>delete</i></button>";
                    var btn_edit = "<button class='btn btn-warning btn-fab btn-fab-mini btn-round' onclick=open_edit('"+data.data[x]['nomor_induk']+"')><i class='material-icons'>edit</i></button>";
                    var sisa_cuti = 12;
                    for (var y = 0; y < data.cuti.length; y++) {
                      if (data.cuti[y]['nomor_induk']==data.data[x]['nomor_induk']){
                        sisa_cuti = sisa_cuti-data.cuti[y]['total_cuti'];
                      }
                    }
                    
                    string_html += "<tr><td>"+data.data[x]['nomor_induk']+"</td><td>"+data.data[x]['nama']+"</td><td>"+data.data[x]['alamat']+"</td><td>"+data.data[x]['tanggal_lahir_format']+"</td><td>"+data.data[x]['tanggal_bergabung_format']+"</td><td>"+sisa_cuti+"</td><td class='text-center'>"+btn_edit+btn_del+"</td></tr>";
                  }
                  $("#main_table").html(string_html);
                  
                  $("#modal_create").modal('hide');
                  
                  $("#list_datas").DataTable({
                      searching: true,bLengthChange: false,rowReorder: true
                  });
                  save_reset_field();
                  $("#modal_success").modal('show');
                  $("#modal_edit").modal('hide');
                  $("#modal_details").modal('hide');
                  $("#modal_create_address").modal('hide');
                }
            },
        });
        $("#modal_save_button").prop('disabled', false);
    }

    function open_delete(id,name) {
      $("#id_delete").val(id);
      name = name.replace(/%20/g, " ");
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
            url: "{{route('delete_karyawan')}}",
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
                    var btn_del = "<button class='btn btn-danger btn-fab btn-fab-mini btn-round' onclick=open_delete('"+data.data[x]['nomor_induk']+"','"+data.data[x]['nama']+"')><i class='material-icons'>delete</i></button>";
                    var btn_edit = "<button class='btn btn-warning btn-fab btn-fab-mini btn-round' onclick=open_edit('"+data.data[x]['nomor_induk']+"')><i class='material-icons'>edit</i></button>";
                    var sisa_cuti = 12;
                    for (var y = 0; y < data.cuti.length; y++) {
                      if (data.cuti[y]['nomor_induk']==data.data[x]['nomor_induk']){
                        sisa_cuti = sisa_cuti-data.cuti[y]['total_cuti'];
                      }
                    }
                    
                    string_html += "<tr><td>"+data.data[x]['nomor_induk']+"</td><td>"+data.data[x]['nama']+"</td><td>"+data.data[x]['alamat']+"</td><td>"+data.data[x]['tanggal_lahir_format']+"</td><td>"+data.data[x]['tanggal_bergabung_format']+"</td><td>"+sisa_cuti+"</td><td class='text-center'>"+btn_edit+btn_del+"</td></tr>";
                  }
                  $("#main_table").html(string_html);
                  $("#modal_delete").modal('hide');
                  
                  $("#list_datas").DataTable({
                      searching: true,bLengthChange: false,rowReorder: true
                  });
                }
            },
        });
    }

    function open_edit(id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: "{{route('show_karyawan')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                "id": id
            },
            success: function (data) {
                var success = data['result'];
                var data    = data['data'];
                var html_   = "";

                console.log(data);
                $("#edit-title").html("");
                $("#edit-title").html(data.nama);
                $("#edit-id_karyawan").val(data.nomor_induk);
                $("#edit-cust-name").val(data.nama);
                $("#edit-cust-address").val(data.alamat);
                $("#edit-input-date").val(parseInt((data.tanggal_lahir).substring(8)));
                console.log(parseInt(((data.tanggal_lahir).substring(5)).substring(0,2)));
                $("#edit-input-month").val(parseInt(((data.tanggal_lahir).substring(5)).substring(0,2)));
                $("#edit-input-year").val(parseInt((data.tanggal_lahir).substring(0,4)));
                $("#edit-id_customer").val(id);
                $("#modal_edit").modal('show');
            },
        });
    }

    function open_modal_create() {
      $("#modal_create").modal('show');
    }
    function open_modal_create_address() {
      $("#modal_create_address").modal('show');
    }
</script>