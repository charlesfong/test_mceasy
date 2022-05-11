@extends('template_admin.template')

@section('Content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-primary">
        <h4 class="card-title ">Report
          {{-- <button type="button" class="btn btn-success" style="float:right" onclick="open_modal_create()">ADD NEW COURIER</button> --}}
        </h4>
      </div>
      <div class="card-body">

        <div class="row" style='margin-bottom:1.5%'>
            <label class="col-sm-2 col-form-label" for="form_customer">Customer</label>
            <div class="col-sm-4">
                <select id='form_customer' name='form_customer' class="form-control" data-style="btn btn-primary btn-round" title="Single Select" onchange_gj="load_anotherfield(this)">
                <option disabled selected>-- SELECT CUSTOMER --</option>
                <option value='ALL'>ALL</option>
                @foreach ($customers as $customer)
                    <option value='{{$customer->id}}' data-addr='{{$customer->address}}' data-phone1='{{$customer->phone1}}' data-phone2='{{$customer->phone2}}' data-phone3='{{$customer->phone3}}' data-email='{{$customer->email}}'>{{$customer->name}}</option>
                @endforeach
                </select>
            </div>
        </div>

        {{-- <div class="row" id="company_section" style='margin-bottom:1.5%'>
            <label class="col-sm-2 col-form-label" for="form_customer">Company</label>
            <div class="col-sm-4">
              <select id='form_company' name='form_company' class="form-control" data-style="btn btn-primary btn-round" title="Single Select">
                <option disabled selected>-- SELECT COMPANY --</option>
              </select>
            </div>
        </div> --}}
  
      </div>
    </div>
  </div>
</div>
@endsection


<script src="{{ asset('/template_admin/js/core/jquery.min.js') }}"></script>
{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
<script>

    $(document).ready( function () {
        $("#list_datas").DataTable({
            // searching: false,bLengthChange: false,rowReorder: true,ordering: false
            searching: true,bLengthChange: false,rowReorder: true
        });
        $('#cust-phone1').mask('000-000-000-000');
        $('#cust-phone2').mask('000-000-000-000');
        $('#cust-phone3').mask('000-000-000-000');
        // $("#form_customer").select2();
    });

    function load_anotherfield(sel){
        if ($('option:selected', sel).val()=='ALL'){
            $("#form_company").html("<option disabled selected>-- SELECT COMPANY --</option>");
            $("#form_company").attr("disabled", true);
        } else {
            $("#form_company").attr("disabled", false);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                url: "{{route('find_company_byId')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'id': $('option:selected', sel).val()
                },
                success: function (data) {
                    var data = JSON.parse(data);
                    $("#form_company").html("<option disabled selected>-- SELECT COMPANY --</option>");
                    for (x=0;x<data.length;x++){
                        $("#form_company").append("<option value='"+data[x]['id']+"' data-addr='"+data[x]['address']+"'>"+data[x]['name']+"</option>");
                    }
                },
            });
        }
    }

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
            url: "{{route('save_courier')}}",
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
                    var name = data.data[x]['name'].replace(/\s/g,'#');
                    var btn = "<td class='text-center'><button class='btn btn-primary btn-fab btn-fab-mini btn-round' onclick=open_detail('"+data.data[x]['id']+"')><i class='material-icons'>view_list</i></button></td>";
                    var btn_del = "<td class='text-center'><button class='btn btn-danger btn-fab btn-fab-mini btn-round' onclick=open_delete('"+data.data[x]['id']+"','"+name+"')><i class='material-icons'>delete</i></button></td>";
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
      name = name.replace(/\#/g,' ');
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
            url: "{{route('delete_courier')}}",
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

                    var name = data.data[x]['name'].replace(/\s/g,'#');
                    var btn = "<td class='text-center'><button class='btn btn-primary btn-fab btn-fab-mini btn-round' onclick=open_detail('"+data.data[x]['id']+"')><i class='material-icons'>view_list</i></button></td>";
                    var btn_del = "<td class='text-center'><button class='btn btn-danger btn-fab btn-fab-mini btn-round' onclick=open_delete('"+data.data[x]['id']+"','"+name+"')><i class='material-icons'>delete</i></button></td>";
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