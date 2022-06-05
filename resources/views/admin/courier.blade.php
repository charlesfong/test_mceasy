@extends('template_admin.template')

@section('Content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-primary">
        <h4 class="card-title ">List Cuti
          {{-- <button type="button" class="btn btn-success" style="float:right" onclick="open_modal_create()">INPUT CUTI</button> --}}
        </h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table border="0" cellspacing="5" cellpadding="5">
            <tbody>
              <tr>
                <td>Minimal Lama Cuti:</td>
                <td><input type="text" id="min" name="min"></td>
              </tr>
            </tbody>
         </table>
          <table id="list_datas" class="table table-striped table-no-bordered table-hover dataTable dtr-inline" cellspacing="0" width="100%" style="width: 100%;" role="grid" aria-describedby="datatables_info">
            <thead class=" text-primary">
              {{-- <th class='text-left'>
                No
              </th> --}}
              <th class='text-left'>
                Nomor Induk
              </th>
              <th class='text-center'>
                Tanggal Cuti
              </th>
              <th class='text-center'>
                Lama Cuti
              </th>
              <th class='text-left'>
                Keterangan
              </th>
            </thead>
            <tbody id='main_table'>
              @foreach ($cuti as $key => $value)
                <tr>
                    <td>
                        @php
                            echo($value->nomor_induk." (".$value->karyawan->nama.")");
                        @endphp
                    </td>
                    <td class='text-center'>{{date('d-M-y',strtotime($value->tanggal_cuti))}}</td>
                    <td class='text-center'>{{$value->lama_cuti}}</td>
                    <td>{{$value->keterangan}}</td>
                    {{-- <td>{{App\Models\cuti::where('nomor_induk',$value->nomor_induk)->sum('lama_cuti')}}</td> --}}
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
                    <h4 class="card-title ">Add New Courier</h4>
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
        var table = $('#list_datas').DataTable();
        $('#min').keyup(function () {
          table.draw();
        });

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var min = parseInt($('#min').val(), 10);
            var age = parseFloat(data[2]) || 0; // use data for the age column
        
            if (
                (isNaN(min)) || (min <= age)
            ) {
                return true;
            }
            return false;
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

    function open_delete(id,name) {
      name = name.replace(/\#/g,' ');
      $("#id_delete").val(id);
      $("#modal_delete_info").html("("+name+")");
      $("#modal_delete").modal('show');
    }

    function open_modal_create() {
      $("#modal_create").modal('show');
    }
</script>