@extends('template_admin.template')

@section('Content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-primary">
        <h4 class="card-title ">Company Info
          <button type="button" class="btn btn-success" style="float:right" onclick="open_modal_create()">ADD NEW SUPPLIER</button>
        </h4>
      </div>
      @php
          // dd($info);
      @endphp
      
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
              <th class='text-center'>
                Details
              </th>
              <th class='text-center'>
                &nbsp;
              </th>
            </thead>
            <tbody id='main_table'>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

<script src="{{ asset('/template_admin/js/core/jquery.min.js') }}"></script>
<script>

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
                    string_html += "<tr><td>"+data.data[x]['name']+"</td><td>"+data.data[x]['address']+"</td><td>"+data.data[x]['phone1']+"</td><td>"+data.data[x]['email']+"</td>"+btn+btn_del+"</tr>";
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

</script>